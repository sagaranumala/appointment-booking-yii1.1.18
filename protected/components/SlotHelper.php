<?php

class SlotHelper
{
    /**
     * Generate slots between start & end time
     */
    public static function generateSlots(
        string $startTime,
        string $endTime,
        int $durationMinutes
    ): array {
        $slots = [];

        $start = strtotime($startTime);
        $end = strtotime($endTime);

        while ($start + ($durationMinutes * 60) <= $end) {
            $slotStart = date('H:i', $start);
            $slotEnd = date('H:i', $start + ($durationMinutes * 60));

            $slots[] = [
                'startTime' => $slotStart,
                'endTime' => $slotEnd
            ];

            $start += ($durationMinutes * 60);
        }

        return $slots;
    }
}
