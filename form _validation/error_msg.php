<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .error {color: #FF0000;}
    </style>
</head>
<body>
    <?php
    // define variables and set to empty values
    $nameErr = $emailErr = $genderErr = $websiteErr = "";
    $name = $email = $gender = $comment = $website = "";
    
    //checks if request method is post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if (empty($_POST["name"])) {
        $nameErr = "Name is required";
      } else {
        $name = test_input($_POST["name"]);
        // check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z-' ]*$/",$name)) {
          $nameErr = "Only letters and white space allowed";
        }
      }
      
      if (empty($_POST["email"])) {
        $emailErr = "Email is required";
      } else {
        $email = test_input($_POST["email"]);
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailErr = "Invalid email format";
        }
      }
        
      if (empty($_POST["website"])) {
        $website = "";
      } else {
        $website = test_input($_POST["website"]);
        // check if URL address syntax is valid (this regular expression also allows dashes in the URL)
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$website)) {
          $websiteErr = "Invalid URL";
        }
      }
    
      if (empty($_POST["comment"])) {
        $comment = "";
      } else {
        $comment = test_input($_POST["comment"]);
      }
    
      if (empty($_POST["gender"])){
        $genderErr="Gender is required";
      }else{
        $gender = test_input($_POST["gender"]);
      }
    }
    function test_input($data) {
        $data = trim($data);  // Remove whitespace from the beginning and end
        $data = stripslashes($data); // Remove backslashes (\)
        $data = htmlspecialchars($data); // Convert special characters to HTML entities
        return $data;
      }
      ?>
      
      <h2>PHP Form Validation Example</h2>
      <!-- ensures the form is submitted to the same script -->
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
        Name: <input type="text" name="name">
        <br><br>
        <span class="error">* <?php echo $nameErr;?></span>
        E-mail: <input type="text" name="email">
        <br><br>
        <span class="error">* <?php echo $emailErr;?></span>
        Website: <input type="text" name="website">
        <br><br>
        <span class="error"><?php echo $websiteErr;?></span>
        Comment: <textarea name="comment" rows="5" cols="40"></textarea>
        <br><br>
        
        Gender:
  <input type="radio" name="gender" <?php if (isset($gender) && $gender=="female") echo "checked";?> value="female">Female
  <input type="radio" name="gender" <?php if (isset($gender) && $gender=="male") echo "checked";?> value="male">Male
  <input type="radio" name="gender" <?php if (isset($gender) && $gender=="other") echo "checked";?> value="other">Other
        <span class="error">* <?php echo $genderErr;?></span><br><br>
        <input type="submit" name="submit" value="Submit">  
      </form>
      
      
      <!-- Used to display submitted data -->
      <?php
      echo "<h2>Your Input:</h2>";
      echo $name;
      echo "<br>";
      echo $email;
      echo "<br>";
      echo $website;
      echo "<br>";
      echo $comment;
      echo "<br>";
      echo $gender;
      ?>
</body>
</html>