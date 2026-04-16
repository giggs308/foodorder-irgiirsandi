<?php
include 'includes/config.php';

echo "<h2>Pesanan Table Structure</h2>";

// Show table structure
echo "<h3>Pesanan Table Structure:</h3>";
$columns = mysqli_query($conn, "DESCRIBE pesanan");
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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

// Show sample data
echo "<h3>Sample Pesanan (first 5):</h3>";
$result = mysqli_query($conn, "SELECT * FROM pesanan LIMIT 5");
if($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1'><tr>";
    // Table headers
    $fields = mysqli_fetch_fields($result);
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    // Table data
    mysqli_data_seek($result, 0);
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "&nbsp;</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in pesanan table or error: " . mysqli_error($conn) . "</p>";
}
?>
