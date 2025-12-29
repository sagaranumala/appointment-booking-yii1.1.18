<?php
class SlotController extends BaseApiController
{
    public function actionAvailable()
    {
        try {
            // Get request data
            $providerUlid = Yii::app()->request->getParam('providerUlid');
            $date = Yii::app()->request->getParam('date');
            
            // For POST requests
            if (!$providerUlid || !$date) {
                $data = json_decode(file_get_contents('php://input'), true);
                $providerUlid = isset($data['providerUlid']) ? $data['providerUlid'] : null;
                $date = isset($data['date']) ? $data['date'] : null;
            }
            
            // Debug logging
            Yii::log("SlotController: providerUlid=$providerUlid, date=$date", CLogger::LEVEL_INFO);
            
            if (!$providerUlid || !$date) {
                $this->sendResponse(400, array(
                    'success' => false,
                    'message' => 'Provider ULID and date are required'
                ));
            }
            
            // Validate date
            $timestamp = strtotime($date);
            if (!$timestamp) {
                $this->sendResponse(400, array(
                    'success' => false,
                    'message' => 'Invalid date format. Use YYYY-MM-DD'
                ));
            }
            
            // Get day of week (0=Sunday, 1=Monday, etc.)
            $dayOfWeek = date('w', $timestamp);
            Yii::log("Day of week for $date: $dayOfWeek", CLogger::LEVEL_INFO);
            
            // Debug: Check if provider exists
            $provider = ServiceProvider::model()->findByAttributes(array(
                'providerUlid' => $providerUlid
            ));
            
            if (!$provider) {
                Yii::log("Provider not found: $providerUlid", CLogger::LEVEL_WARNING);
                $this->sendResponse(404, array(
                    'success' => false,
                    'message' => 'Provider not found'
                ));
            }
            
            // Get provider's availability for this day of week
            $availability = ProviderAvailability::model()->findByAttributes(array(
                'providerUlid' => $providerUlid,
                'dayOfWeek' => $dayOfWeek,
                'status' => 1
            ));
            
            Yii::log("Availability found: " . ($availability ? 'Yes' : 'No'), CLogger::LEVEL_INFO);
            
            if (!$availability) {
                // Return empty slots if no availability for this day
                $this->sendResponse(200, array(
                    'success' => true,
                    'data' => array(
                        'date' => $date,
                        'dayOfWeek' => $dayOfWeek,
                        'slots' => array(),
                        'availability' => null,
                        'message' => 'Provider is not available on this day'
                    )
                ));
            }
            
            // Debug availability data
            Yii::log("Availability: startTime=" . $availability->startTime . 
                    ", endTime=" . $availability->endTime, CLogger::LEVEL_INFO);
            
            // Get booked appointments for this date
            $bookedAppointments = Appointment::model()->findAllByAttributes(array(
                'providerUlid' => $providerUlid,
                'appointmentDate' => $date,
                'status' => 'booked'
            ));
            
            Yii::log("Booked appointments: " . count($bookedAppointments), CLogger::LEVEL_INFO);
            
            // Generate 1-hour slots
            $slots = array();
            $startTime = strtotime($availability->startTime);
            $endTime = strtotime($availability->endTime);
            $slotDuration = 60 * 60; // 1 hour in seconds
            
            // Validate time format
            if ($startTime === false || $endTime === false) {
                Yii::log("Invalid time format: start=" . $availability->startTime . 
                        ", end=" . $availability->endTime, CLogger::LEVEL_ERROR);
                $this->sendResponse(500, array(
                    'success' => false,
                    'message' => 'Invalid time format in availability'
                ));
            }
            
            while ($startTime < $endTime) {
                $slotEnd = $startTime + $slotDuration;
                
                if ($slotEnd <= $endTime) {
                    $slotStartStr = date('H:i:s', $startTime);
                    $slotEndStr = date('H:i:s', $slotEnd);
                    
                    // Check if slot is available (not booked)
                    $isAvailable = true;
                    foreach ($bookedAppointments as $appointment) {
                        $appStart = strtotime($appointment->startTime);
                        $appEnd = strtotime($appointment->endTime);
                        
                        // Check for overlap: new slot starts before existing ends AND new slot ends after existing starts
                        if ($startTime < $appEnd && $slotEnd > $appStart) {
                            $isAvailable = false;
                            break;
                        }
                    }
                    
                    if ($isAvailable) {
                        $slots[] = array(
                            'startTime' => $slotStartStr,
                            'endTime' => $slotEndStr,
                            'displayTime' => date('g:i A', $startTime) . ' - ' . date('g:i A', $slotEnd)
                        );
                    }
                }
                
                $startTime += $slotDuration;
            }
            
            Yii::log("Generated " . count($slots) . " available slots", CLogger::LEVEL_INFO);
            
            $this->sendResponse(200, array(
                'success' => true,
                'data' => array(
                    'date' => $date,
                    'dayOfWeek' => $dayOfWeek,
                    'slots' => $slots,
                    'availability' => array(
                        'startTime' => $availability->startTime,
                        'endTime' => $availability->endTime
                    ),
                    'providerInfo' => array(
                        'name' => $provider->user->fullName ?? 'Provider',
                        'hourlyRate' => $provider->hourlyRate
                    )
                )
            ));
            
        } catch (Exception $e) {
            Yii::log("SlotController error: " . $e->getMessage(), CLogger::LEVEL_ERROR);
            $this->sendResponse(500, array(
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    /**
     * Alternative method for GET requests
     */
    public function actionGetAvailableSlots()
    {
        $providerUlid = Yii::app()->request->getQuery('providerUlid');
        $date = Yii::app()->request->getQuery('date');
        
        // Forward to main action
        $this->actionAvailable();
    }

    // SlotController.php - Add a new action for getting available dates
public function actionAvailableDates()
{
    try {
        $providerUlid = Yii::app()->request->getParam('providerUlid');
        $month = Yii::app()->request->getParam('month', date('Y-m'));
        $year = Yii::app()->request->getParam('year', date('Y'));
        
        if (!$providerUlid) {
            $this->sendResponse(400, array(
                'success' => false,
                'message' => 'Provider ULID is required'
            ));
        }
        
        // Get provider's weekly availability
        $weeklyAvailability = ProviderAvailability::model()->findAllByAttributes(array(
            'providerUlid' => $providerUlid,
            'status' => 1
        ));
        
        if (empty($weeklyAvailability)) {
            $this->sendResponse(200, array(
                'success' => true,
                'data' => array(
                    'availableDates' => array(),
                    'message' => 'Provider has no set availability'
                )
            ));
        }
        
        // Create an array of available days (0-6 where Sunday=0)
        $availableDays = array();
        foreach ($weeklyAvailability as $avail) {
            $availableDays[] = $avail->dayOfWeek;
        }
        
        // Get booked appointments for this provider in the requested month
        $startDate = date('Y-m-01', strtotime("$month-01"));
        $endDate = date('Y-m-t', strtotime("$month-01"));
        
        $bookedAppointments = Appointment::model()->findAll(array(
            'condition' => 'providerUlid = :provider AND appointmentDate BETWEEN :start AND :end AND status = "booked"',
            'params' => array(
                ':provider' => $providerUlid,
                ':start' => $startDate,
                ':end' => $endDate
            )
        ));
        
        // Create a map of fully booked dates
        $fullyBookedDates = array();
        foreach ($bookedAppointments as $appointment) {
            $date = $appointment->appointmentDate;
            
            // For simplicity, we'll mark a date as fully booked if it has appointments
            // You could make this smarter by checking if all time slots are booked
            if (!isset($fullyBookedDates[$date])) {
                $fullyBookedDates[$date] = 0;
            }
            $fullyBookedDates[$date]++;
        }
        
        // Generate available dates for the month
        $availableDates = array();
        $current = strtotime($startDate);
        $end = strtotime($endDate);
        
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $dayOfWeek = date('w', $current); // 0=Sunday, 1=Monday, etc.
            $today = date('Y-m-d');
            
            // Check if date is in the future and provider is available on this day
            if ($date >= $today && in_array($dayOfWeek, $availableDays)) {
                
                // Check if date is fully booked
                $isFullyBooked = false;
                if (isset($fullyBookedDates[$date])) {
                    // Get availability for this day
                    $availability = null;
                    foreach ($weeklyAvailability as $avail) {
                        if ($avail->dayOfWeek == $dayOfWeek) {
                            $availability = $avail;
                            break;
                        }
                    }
                    
                    if ($availability) {
                        // Calculate total available hours
                        $start = strtotime($availability->startTime);
                        $endTime = strtotime($availability->endTime);
                        $totalHours = ($endTime - $start) / 3600;
                        
                        // If booked appointments cover all hours, mark as fully booked
                        if ($fullyBookedDates[$date] >= $totalHours) {
                            $isFullyBooked = true;
                        }
                    }
                }
                
                if (!$isFullyBooked) {
                    $availableDates[] = array(
                        'date' => $date,
                        'dayOfWeek' => $dayOfWeek,
                        'dayName' => date('l', $current),
                        'displayDate' => date('M j, Y', $current),
                        'isToday' => ($date == $today),
                        'isPast' => ($date < $today)
                    );
                }
            }
            
            $current = strtotime('+1 day', $current);
        }
        
        $this->sendResponse(200, array(
            'success' => true,
            'data' => array(
                'month' => date('F Y', strtotime("$month-01")),
                'availableDates' => $availableDates,
                'totalAvailable' => count($availableDates)
            )
        ));
        
    } catch (Exception $e) {
        Yii::log("AvailableDates error: " . $e->getMessage(), CLogger::LEVEL_ERROR);
        $this->sendResponse(500, array(
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ));
    }
}
}