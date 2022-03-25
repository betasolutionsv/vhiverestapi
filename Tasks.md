21032022
# Check API Code
# checked some api code
# api.php scratch card 14,22 line

# Design Prototype

# Dump some dummy data into database
# dumped some dummy data database

# Create API and test it
# Checking live goffix API

# services:
# login api
# otp authentication
# phonenumber verification
# verify password
# getphone number
# 








# api
<?php
header('Content-Type: application/json');

        

$con = mysqli_connect("localhost","root","","vhive");
$command = "SELECT * FROM vsitor";
$result = (mysqli_query($con, $command));
print_r($result);
// echo(json_encode($result));
?>   












