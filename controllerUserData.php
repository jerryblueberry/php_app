<?php 
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require'PHPMailer/Exception.php';
require'PHPMailer/PHPMailer.php';
require'PHPMailer/SMTP.php';

require "connection.php";
$email = "";
$name = "";
$errors = array();

//if user signup button
if(isset($_POST['signup'])){
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    //  new code check
    


    
    //  end of new code



    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }
    $email_check = "SELECT * FROM usertable WHERE email = '$email'";
    $res = mysqli_query($con, $email_check);
    if(mysqli_num_rows($res) > 0){
        $errors['email'] = "Email that you have entered is already exist!";
    }
    if(count($errors) === 0){
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = rand(999999, 111111);
        $status = "notverified";
        $insert_data = "INSERT INTO usertable (name, email, password, code, status)
                        values('$name', '$email', '$encpass', '$code', '$status')";
        $data_check = mysqli_query($con, $insert_data);
        if($data_check){
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host= 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sajankoi46@gmail.com'; //Your gmail
            $mail->Password = 'hjcwyzppgkxvfusz';
            $mail->SMTPSecure = 'ssl';
            $mail->Port= 465;
            $subject = "Email Verification code sent";
            $message = "Your code is $code";
           

            $mail->setFrom('sajankoi46@gmail.com');
            $mail->addAddress($_POST['email']);
            $mail->isHTML(true);
            $mail->Subject =$subject;
            $mail->Body =$message;

            $mail->send();
            echo"<script>
            alert('Sent Successfully');
            document.location.href = 'user-otp.php'
            </script>";
        }


        //     $subject = "Email Verification Code";
        //     $message = "Your verification code is $code";
        //     $sender = "From: sajankoi46@gmail.com";
        //     if(mail($email, $subject, $message, $sender)){
        //         $info = "We've sent a verification code to your email - $email";
        //         $_SESSION['info'] = $info;
        //         $_SESSION['email'] = $email;
        //         $_SESSION['password'] = $password;
        //         header('location: user-otp.php');
        //         exit();
        //     }else{
        //         $errors['otp-error'] = "Failed while sending code!";
        //     }
        // }else{
        //     $errors['db-error'] = "Failed while inserting data into database!";
        // }
    }

}
    //if user click verification code submit button
    if(isset($_POST['check'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
        $check_code = "SELECT * FROM usertable WHERE code = $otp_code";
        $code_res = mysqli_query($con, $check_code);
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $fetch_code = $fetch_data['code'];
            $email = $fetch_data['email'];
            $code = 0;
            $status = 'verified';
            $update_otp = "UPDATE usertable SET code = $code, status = '$status' WHERE code = $fetch_code";
            $update_res = mysqli_query($con, $update_otp);
            if($update_res){
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                header('location: home.php');
                exit();
            }else{
                $errors['otp-error'] = "Failed while updating code!";
            }
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    //if user click login button
    if(isset($_POST['login'])){
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $check_email = "SELECT * FROM usertable WHERE email = '$email'";
        $res = mysqli_query($con, $check_email);
        if(mysqli_num_rows($res) > 0){
            $fetch = mysqli_fetch_assoc($res);
            $fetch_pass = $fetch['password'];
            if(password_verify($password, $fetch_pass)){
                $_SESSION['email'] = $email;
                $status = $fetch['status'];
                if($status == 'verified'){
                  $_SESSION['email'] = $email;
                  $_SESSION['password'] = $password;
                    header('location: home.php');
                }else{
                    $info = "It's look like you haven't still verify your email - $email";
                    $_SESSION['info'] = $info;
                    header('location: user-otp.php');
                }
            }else{
                $errors['email'] = "Incorrect email or password!";
            }
        }else{
            $errors['email'] = "It's look like you're not yet a member! Click on the bottom link to signup.";
        }
    }

    //if user click continue button in forgot password form
    //  new code
    if (isset($_POST['check-email'])) {
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $check_email_query = "SELECT * FROM usertable WHERE email='$email'";
        $run_query = mysqli_query($con, $check_email_query);
        if (mysqli_num_rows($run_query) > 0) {
            $code = rand(111111, 999999);
            $update_query = "UPDATE login SET code = $code WHERE email = '$email'";
            $run_update_query = mysqli_query($con, $update_query);
            if ($run_update_query) {
                require_once('phpmailer/PHPMailerAutoload.php');
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = 'your-email@gmail.com';
                $mail->Password = 'your-email-password';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('your-email@gmail.com');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $subject = "Password Reset Code";
                $message = "Your password reset code is $code";
                $mail->Subject = $subject;
                $mail->Body = $message;
                if ($mail->send()) {
                    $_SESSION['email'] = $email;
                    header('location: reset-code.php');
                    exit();
                } else {
                    $errors['otp-error'] = "Failed while sending code!";
                }
            } else {
                $errors['db-error'] = "Something went wrong!";
            }
        } else {
            $errors['email'] = "This email address does not exist!";
        }
    }
    //  end code
    
    // if(isset($_POST['check-email'])){
    //     $email = mysqli_real_escape_string($con, $_POST['email']);
    //     $check_email = "SELECT * FROM usertable WHERE email='$email'";
    //     $run_sql = mysqli_query($con, $check_email);
    //     if(mysqli_num_rows($run_sql) > 0){
    //         $code = rand(999999, 111111);
    //         $insert_code = "UPDATE login SET code = $code WHERE email = '$email'";
    //         $run_query =  mysqli_query($con, $insert_code);
    //         if($run_query){
    //             //  changed 1 line added this below code
    //             // require_once('phpmailer/PHPMailerAutoload.php');
    //             $mail = new PHPMailer(true);
    //             $mail->isSMTP();
    //             $mail->Host = "smtp.gmail.com";
    //             $mail->SMTPAuth =  true;
    //             $mail->Username = 'sajankoi46@gmail.com';
    //             $mail->Password = 'hjcwyzppgkxvfusz';
    //             $mail->SMTPSecure = 'tsl';
    //             $mail->Port = 587;
    //             $_SESSION['email'] = $email;
    //             $subject = "Password Reset Code ";
    //             $message = "Your password reset code is $code";
    //             $_SESSION['email']  = $email;
    //             $mail->setFrom('sajankoi46@gmail.com');
    //             $mail->addAddress($email);
    //             $mail->isHTML(true);
                


              
    //             $subject = "Password Reset Code";
    //             $message = "Your password reset code is $code";
               
    //             //  end of the logic code

    //         //    if($mail->send()){
    //         //     echo"A password reset code has been sent to your email address";
    //         //    }else{
    //         //     echo"Unable to generate reset code.Please try again";
    //         //    }

    //         //     echo"<script>alert('Sent successfully');
    //         //     document.location.href = 'reset-code.php';
                
    //         //     </script>";
                





    //             // $subject = "Password Reset Code";
    //             // $message = "Your password reset code is $code";
    //             $sender = "From: sajankoi46@gmail.com";
    //             if(mail($email, $subject, $message, $sender)){
    //                 $info = "We've sent a passwrod reset otp to your email - $email";
    //                 $_SESSION['info'] = $info;
    //                 $_SESSION['email'] = $email;
    //                 header('location: reset-code.php');
    //                 exit();
    //             }else{
    //                 $errors['otp-error'] = "Failed while sending code!";
    //             }
    //         }else{
    //             $errors['db-error'] = "Something went wrong!";
             
    //         }
    //     }else{
    //         $errors['email'] = "This email address does not exist!";
    //     }
    // }
    //if user click check reset otp button
    if(isset($_POST['check-reset-otp'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
        $check_code = "SELECT * FROM usertable WHERE code = $otp_code";
        $code_res = mysqli_query($con, $check_code);
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $email = $fetch_data['email'];
            $_SESSION['email'] = $email;
            $info = "Please create a new password that you don't use on any other site.";
            $_SESSION['info'] = $info;
            header('location: new-password.php');
            exit();
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    //if user click change password button
    if(isset($_POST['change-password'])){
        $_SESSION['info'] = "";
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
        if($password !== $cpassword){
            $errors['password'] = "Confirm password not matched!";
        }else{
            $code = 0;
            $email = $_SESSION['email']; //getting this email using session
            $encpass = password_hash($password, PASSWORD_BCRYPT);
            $update_pass = "UPDATE usertable SET code = $code, password = '$encpass' WHERE email = '$email'";
            $run_query = mysqli_query($con, $update_pass);
            if($run_query){
                $info = "Your password changed. Now you can login with your new password.";
                $_SESSION['info'] = $info;
                header('Location: password-changed.php');
            }else{
                $errors['db-error'] = "Failed to change your password!";
            }
        }
    }
    
   //if login now button click
    if(isset($_POST['login-now'])){
        header('Location: login-user.php');
    }
?>