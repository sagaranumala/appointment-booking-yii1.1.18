    // Function to get user details using JWT token
const getUserDetails = async (token: string) => {
  try {
    const response = await fetch('http://localhost:8080/index.php?r=auth/getUser', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to fetch user details');
    }

    return data.data; // Returns { user: {...}, token_info: {...} }
  } catch (error) {
    console.error('Error fetching user details:', error);
    return null;
  }
};

// Usage
const token = data.data.token;
const userData = await getUserDetails(token);
if (userData) {
  console.log('TEST-User:', userData.user);
  console.log('Token info:', userData.token_info);
}
