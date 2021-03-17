<?php

function login($username, $password, $ip)
{   
    $log_attempts = 'LoggedAttempts';
    $_SESSION[$log_attempts] = 0; //--> this is to reset the logout
    //if there have been attempts to log in and the number of attempts is greater than or equal to 3, exit login function and return lockout message
    if(isset($_SESSION[$log_attempts])&& $_SESSION[$log_attempts] >=3){
        return "3 failed attempts - You are now locked out - Contact Admin to get back in";
    }
    $pdo = Database::getInstance()->getConnection();
    ##finish the following query
    $get_user_query = 'SELECT * FROM `tbl_user` WHERE user_name = :username';

    $user_set = $pdo->prepare($get_user_query);
    $user_set->execute(
        array(
            ':username'=>$username            
        )
    );
//refactored this part of the code to include checking the encrypted password
    $found_user = $user_set->fetch(PDO::FETCH_ASSOC);
    $verify = password_verify($password, $found_user['user_pass']);
  
    if ($found_user && $verify) {

        
        
        $expired = //???? define this
        
        //check if it is new user && the expiration date hasnt expired
        if($found_user['login_num'] <= 0 && $expired){
            redirect_to("admin_login.php?error_message=Your Account Is Expired");
        }


        //we found the user in the DB, get them in!
        $found_user_id = $found_user['user_id'];

        //write the session info
        $_SESSION['user_id'] = $found_user_id;
        $_SESSION['user_name'] = $found_user['user_fname'];
        $_SESSION['user_level'] = $found_user['user_level'];

        //write the last session date and time
        $_SESSION['last_login'] = $found_user['user_date'];
        //update the session date and time to current data and time
        $update_time_query = 'UPDATE tbl_user SET user_date = NOW(), user_ip = :user_ip, login_num = login_num+1 WHERE user_id=:user_id';
        $update_time_set = $pdo->prepare($update_time_query);
        $update_time_set->execute(
            array(
                ':user_ip'=>$ip,
                ':user_id'=>$found_user_id 
            )
        );
    
        //write the login number so it can be set on the page
        $_SESSION['login_num'] = $found_user['login_num'];
        //redirect user back to index.php
        if($found_user['login_num'] <= 0){
            redirect_to('admin_edituser.php');
        }
        redirect_to('index.php');

    } else {
        //this is an invalid attempt reject it
      
        //if they get it wrong, log the attempt in long_attemps and for each of those add one to the number
        if(isset($_SESSION[$log_attempts])){
            //add one to logged attemps if the username/pass is invalid
            $_SESSION[$log_attempts]++;
            return 'Invalid username/password. Try again.';
        }else{
            //if they successfully login - reset the login_attempts to zero
            $_SESSION[$log_attempts] = 0; 
        }

        

    }
}

function confirm_logged_in(){
    if(!isset($_SESSION['user_id'])){
        redirect_to("admin_login.php");
    }
}

function logout() {
    session_destroy();
    redirect_to('admin_login.php');
}
?>