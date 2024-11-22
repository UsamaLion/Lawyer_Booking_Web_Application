<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

// Create uploads directory if it doesn't exist
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$username = $email = $password = $confirm_password = $phone_number = $license_number = $specialization = $city = $bio = $education = $practice_areas = $courts = $fee = "";
$username_err = $email_err = $password_err = $confirm_password_err = $phone_number_err = $license_number_err = $specialization_err = $city_err = $bio_err = $education_err = $practice_areas_err = $courts_err = $fee_err = $profile_picture_err = $license_id_card_err = $education_documents_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invalid email format.";
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate phone number
    if (empty(trim($_POST["phone_number"]))) {
        $phone_number_err = "Please enter a phone number.";
    } else {
        $phone_number = trim($_POST["phone_number"]);
    }
    
    // Validate specialization
    if (empty(trim($_POST["specialization"]))) {
        $specialization_err = "Please enter a specialization.";
    } else {
        $specialization = trim($_POST["specialization"]);
    }
    
    // Validate city
    if (empty(trim($_POST["city"]))) {
        $city_err = "Please enter a city.";
    } else {
        $city = trim($_POST["city"]);
    }

    // Validate license number
    if (empty(trim($_POST["license_number"]))) {
        $license_number_err = "Please enter a license number.";
    } else {
        $license_number = trim($_POST["license_number"]);
    }

    // Validate bio
    if (empty(trim($_POST["bio"]))) {
        $bio_err = "Please enter a bio.";
    } else {
        $bio = trim($_POST["bio"]);
    }

    // Validate education
    if (empty(trim($_POST["education"]))) {
        $education_err = "Please enter your education.";
    } else {
        $education = trim($_POST["education"]);
    }

    // Validate practice areas
    if (empty(trim($_POST["practice_areas"]))) {
        $practice_areas_err = "Please enter your practice areas.";
    } else {
        $practice_areas = trim($_POST["practice_areas"]);
    }

    // Validate courts
    if (empty(trim($_POST["courts"]))) {
        $courts_err = "Please enter the courts you practice in.";
    } else {
        $courts = trim($_POST["courts"]);
    }

    // Validate fee
    if (empty(trim($_POST["fee"]))) {
        $fee_err = "Please enter your fee.";
    } else {
        $fee = trim($_POST["fee"]);
        if (!is_numeric($fee) || $fee < 0) {
            $fee_err = "Please enter a valid fee.";
        }
    }

    // Validate file uploads
    $target_dir = "uploads/";
    $profile_picture = $license_id_card = $education_documents = "";

    function generate_unique_filename($original_filename) {
        $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        return uniqid() . '.' . $extension;
    }

    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $profile_picture = $target_dir . generate_unique_filename($_FILES["profile_picture"]["name"]);
        if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture)) {
            $profile_picture_err = "Failed to upload profile picture.";
        }
    } else {
        $profile_picture_err = "Please upload a profile picture.";
    }

    if (isset($_FILES["license_id_card"]) && $_FILES["license_id_card"]["error"] == 0) {
        $license_id_card = $target_dir . generate_unique_filename($_FILES["license_id_card"]["name"]);
        if (!move_uploaded_file($_FILES["license_id_card"]["tmp_name"], $license_id_card)) {
            $license_id_card_err = "Failed to upload license ID card.";
        }
    } else {
        $license_id_card_err = "Please upload your license ID card.";
    }

    if (isset($_FILES["education_documents"]) && $_FILES["education_documents"]["error"] == 0) {
        $education_documents = $target_dir . generate_unique_filename($_FILES["education_documents"]["name"]);
        if (!move_uploaded_file($_FILES["education_documents"]["tmp_name"], $education_documents)) {
            $education_documents_err = "Failed to upload education documents.";
        }
    } else {
        $education_documents_err = "Please upload your education documents.";
    }
    
    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && 
        empty($phone_number_err) && empty($specialization_err) && empty($city_err) && empty($license_number_err) && 
        empty($bio_err) && empty($education_err) && empty($practice_areas_err) && empty($courts_err) && 
        empty($fee_err) && empty($profile_picture_err) && empty($license_id_card_err) && empty($education_documents_err)) {
        
        // Prepare an insert statement for Users table
        $sql = "INSERT INTO Users (username, email, password, user_type, is_verified) VALUES (?, ?, ?, 'lawyer', 0)";
         
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_email, $param_password);
            
            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                
                // Prepare an insert statement for Lawyers table
                $sql = "INSERT INTO Lawyers (user_id, license_number, specialization, city, bio, education, practice_areas, courts, fee, profile_picture, license_id_card, education_documents, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "isssssssdsss", $param_user_id, $param_license_number, $param_specialization, $param_city, $param_bio, $param_education, $param_practice_areas, $param_courts, $param_fee, $param_profile_picture, $param_license_id_card, $param_education_documents, $param_phone_number);
                    
                    // Set parameters
                    $param_user_id = $user_id;
                    $param_license_number = $license_number;
                    $param_specialization = $specialization;
                    $param_city = $city;
                    $param_bio = $bio;
                    $param_education = $education;
                    $param_practice_areas = $practice_areas;
                    $param_courts = $courts;
                    $param_fee = $fee;
                    $param_profile_picture = $profile_picture;
                    $param_license_id_card = $license_id_card;
                    $param_education_documents = $education_documents;
                    $param_phone_number = $phone_number;
                    
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Create verification token
                        $verification_token = bin2hex(random_bytes(16));
                        
                        $sql = "INSERT INTO email_verification (user_id, token) VALUES (?, ?)";
                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "is", $user_id, $verification_token);
                            if (mysqli_stmt_execute($stmt)) {
                                // Send verification email
                                $to = $email;
                                $subject = "Email Verification for Lawyer Account";
                                $message = "Click the following link to verify your email: http://yourdomain.com/verify_email.php?token=" . $verification_token;
                                $headers = "From: noreply@yourdomain.com";
                                
                                if (mail($to, $subject, $message, $headers)) {
                                    header("location: registration_success.php");
                                } else {
                                    echo "Error sending verification email. Please try again later.";
                                }
                            } else {
                                echo "Error creating verification token. Please try again later.";
                            }
                        }
                    } else {
                        echo "Something went wrong. Please try again later.";
                    }
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<?php include "includes/header.php"; ?>

<h2>Lawyer Registration</h2>
<p>Please fill this form to create a lawyer account.</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
        <span class="invalid-feedback"><?php echo $username_err; ?></span>
    </div>    
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
        <span class="invalid-feedback"><?php echo $email_err; ?></span>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
        <span class="invalid-feedback"><?php echo $password_err; ?></span>
    </div>
    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
    </div>
    <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone_number" class="form-control <?php echo (!empty($phone_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone_number; ?>">
        <span class="invalid-feedback"><?php echo $phone_number_err; ?></span>
    </div>
    <div class="form-group">
        <label>License Number</label>
        <input type="text" name="license_number" class="form-control <?php echo (!empty($license_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $license_number; ?>">
        <span class="invalid-feedback"><?php echo $license_number_err; ?></span>
    </div>
    <div class="form-group">
        <label>Specialization</label>
        <input type="text" name="specialization" class="form-control <?php echo (!empty($specialization_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $specialization; ?>">
        <span class="invalid-feedback"><?php echo $specialization_err; ?></span>
    </div>
    <div class="form-group">
        <label>City</label>
        <input type="text" name="city" class="form-control <?php echo (!empty($city_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $city; ?>">
        <span class="invalid-feedback"><?php echo $city_err; ?></span>
    </div>
    <div class="form-group">
        <label>Bio</label>
        <textarea name="bio" class="form-control <?php echo (!empty($bio_err)) ? 'is-invalid' : ''; ?>"><?php echo $bio; ?></textarea>
        <span class="invalid-feedback"><?php echo $bio_err; ?></span>
    </div>
    <div class="form-group">
        <label>Education</label>
        <textarea name="education" class="form-control <?php echo (!empty($education_err)) ? 'is-invalid' : ''; ?>"><?php echo $education; ?></textarea>
        <span class="invalid-feedback"><?php echo $education_err; ?></span>
    </div>
    <div class="form-group">
        <label>Practice Areas</label>
        <input type="text" name="practice_areas" class="form-control <?php echo (!empty($practice_areas_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $practice_areas; ?>">
        <span class="invalid-feedback"><?php echo $practice_areas_err; ?></span>
    </div>
    <div class="form-group">
        <label>Courts</label>
        <input type="text" name="courts" class="form-control <?php echo (!empty($courts_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $courts; ?>">
        <span class="invalid-feedback"><?php echo $courts_err; ?></span>
    </div>
    <div class="form-group">
        <label>Fee</label>
        <input type="number" step="0.01" name="fee" class="form-control <?php echo (!empty($fee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $fee; ?>">
        <span class="invalid-feedback"><?php echo $fee_err; ?></span>
    </div>
    <div class="form-group">
        <label>Profile Picture</label>
        <input type="file" name="profile_picture" class="form-control-file <?php echo (!empty($profile_picture_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $profile_picture_err; ?></span>
    </div>
    <div class="form-group">
        <label>License ID Card</label>
        <input type="file" name="license_id_card" class="form-control-file <?php echo (!empty($license_id_card_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $license_id_card_err; ?></span>
    </div>
    <div class="form-group">
        <label>Education Documents</label>
        <input type="file" name="education_documents" class="form-control-file <?php echo (!empty($education_documents_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $education_documents_err; ?></span>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <input type="reset" class="btn btn-secondary ml-2" value="Reset">
    </div>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</form>

<?php include "includes/footer.php"; ?>
