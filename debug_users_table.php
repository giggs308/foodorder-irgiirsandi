<?php
include 'includes/config.php';

echo "<h2>Checking Database Connection</h2>";
if ($conn) {
    echo "<p style='color:green'>✅ Connected to database successfully</p>";
    
    // Check if users table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if(mysqli_num_rows($result) > 0) {
        echo "<p style='color:green'>✅ Users table exists</p>";
        
        // Show table structure
        echo "<h3>Users Table Structure:</h3>";
        $columns = mysqli_query($conn, "DESCRIBE users");
        echo "<table border='1'>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Key</th>
                    <th>Default</th>
                    <th>Extra</th>
                </tr>";
        while($row = mysqli_fetch_assoc($columns)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample users
        echo "<h3>Sample Users (first 5):</h3>";
        $users = mysqli_query($conn, "SELECT * FROM users LIMIT 5");
        if(mysqli_num_rows($users) > 0) {
            echo "<table border='1'><tr>";
            // Table headers
            $fields = mysqli_fetch_fields($users);
            foreach ($fields as $field) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            
            // Table data
            while($user = mysqli_fetch_assoc($users)) {
                echo "<tr>";
                foreach($user as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange'>No users found in the database.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Users table does not exist</p>";
    }
} else {
    echo "<p style='color:red'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
}

// Close connection
mysqli_close($conn);
?>

<h3>PHP Session Information:</h3>
<pre>
<?php 
session_start();
print_r($_SESSION); 
?>
</pre>
