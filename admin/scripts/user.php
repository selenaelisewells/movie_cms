<?php

function getUserLevelMap()
{
    return array(
        '0' => 'Web Editor',
        '1' => 'Web Admin',
        '2' => 'Web Super Admin',
    );
}

function getCurrentUserLevel()
{
    $user_level_map = getUserLevelMap();

    if (isset($_SESSION['user_level']) && array_key_exists($_SESSION['user_level'], $user_level_map)) {
        return $user_level_map[$_SESSION['user_level']];
    } else {
        return "Unrecognized";
    }
}


function createUser($user_data){

    if(empty($user_data['username']) || isUsernameExists($user_data['username'])){
        return 'Username is Invalid or Already Exists';
    }
    ##RANDOMLY GENERATE A PASSWORD HERE
    $random_password = createRandomPassword(); 

    ##ENCRYPT THE RANDOM PASSWORD HERE
    $encrypted_password = createEncryptedPassword($random_password);   

    ##1. Run the proper SQL query to insert user
    $pdo = Database::getInstance()->getConnection();
    ##EDIT HERE SO THAT THE INPUT PASSWORD ISNT THERE- Save the new encrypted password here (add to db)
    $create_user_query = 'INSERT INTO tbl_user(user_fname, user_name, user_pass, user_email, user_level)';
    $create_user_query .= 'VALUES(:fname,:username,:password,:email, :user_level)';

 
    $create_user_set = $pdo->prepare($create_user_query);
    $create_user_result = $create_user_set->execute(
        array(
            ":fname"=>$user_data["fname"],
            ":username"=>$user_data["username"],
            ":password"=>$encrypted_password,
            ":email"=>$user_data["email"],
            ":user_level"=>$user_data["user_level"]
        )
    );

    ##2. Redirect to index.php if we created user successfully, maybe with some message?,
    ##   otherwise show the error message

    if($create_user_result){
        ##EMAIL USER THEIR CREDENTIALS
        ##pass in the user info we grabbed before these lines
        ##here will use the plain text password
        sendRegistrationEmail($user_data["username"],$random_password,$user_data["email"]);
        ##THIS IS FOR TESTING PURPOSES BECAUSE I DON'T HAVE A LIVE SERVER TO TEST THE EMAIL ON
        var_dump($random_password);
        die;
        redirect_to('index.php');
    }else{
        return 'The user did not go through!!';
    }
}


##function to create random password goes here 
function createRandomPassword(){
    //these are the character options
    $characterOptions = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $rand_password = array();
    //account for index lengths
    $optionsLength = strlen($characterOptions) - 1;
    //loop over and choose a random character
    for ($i = 0; $i < 7; $i++) {
        $number = rand(0, $optionsLength);
        $rand_password[] = $characterOptions[$number];
    }
    return implode($rand_password); //turn the array into a string
}

##function that encrypts the password that we randomly generated

function createEncryptedPassword($password){
    //the has of the password that can be stored in a database
    return password_hash($password, PASSWORD_DEFAULT);  
}

##create function to send email - requires username, password and email
function sendRegistrationEmail($username, $password, $email){
     ##setup email subject, message of the email and link in the login 
    $admin_url = $_SERVER['HTTP_HOST'].'/wells_s_3014_r2/admin/admin_login.php';
    $email_subject = 'New User Credentials for '. $username;
    $email_message = sprintf('Username: %s, Password: %s, Login Here: %s', $username, $password, $admin_url);
    ##set headers on the email
    $email_headers = array(
        'From' =>'donotreply@moviescms.com',
        'Reply-To'=> $email
    );

   return mail($email,$email_subject,$email_message,$email_headers);  

}

function getSingleUser($id)
{
    $pdo = Database::getInstance()->getConnection();

    ## TODO: finish the following SQL query so that it can fetch all data about that user with user_id = $id
    $get_user_query = 'SELECT * FROM tbl_user WHERE user_id = :id';
    $get_user_set   = $pdo->prepare($get_user_query);
    $results        = $get_user_set->execute(
        array(
            ':id' => $id,
        )
    );

    if ($results && $get_user_set->rowCount()) {
        return $get_user_set;
    } else {
        return false;
    }
}

function getAllUsers(){
    $pdo = Database::getInstance()->getConnection();

    $get_user_query = 'SELECT * FROM tbl_user';
    $users = $pdo->query($get_user_query);

    if($users){
        return $users;
    }else{
        return false;
    }
}

function deleteUser($user_id){
    $pdo = Database::getInstance()->getConnection();
    $delete_user_query = 'DELETE FROM tbl_user WHERE user_id = :id';
    $delete_user_set = $pdo->prepare($delete_user_query);
    $delete_user_result = $delete_user_set->execute(
        array(
            ':id'=>$user_id
        )
    );

    if($delete_user_result && $delete_user_set->rowCount()>0){
        redirect_to('admin_deleteuser.php');
    }else{
        return false;
    }
}

function editUser($user_data)
{
    if (empty($user_data['username']) || isUsernameExists($user_data['username'])) {
        return 'Username is invalid!!';
    }

    $pdo = Database::getInstance()->getConnection();

    ## TODO: finish the following lines, so that your user profile is updated
    $update_user_query  = 'UPDATE tbl_user SET user_fname = :fname, user_name=:username, user_pass=:password, user_email=:email, user_level=:level WHERE user_id=:id';
    $update_user_set    = $pdo->prepare($update_user_query);
    $update_user_result = $update_user_set->execute(
        array(
            ':fname'    => $user_data['fname'],
            ':username' => $user_data['username'],
            ':password' => $user_data['password'],
            ':email'    => $user_data['email'],
            ':level'    => $user_data['user_level'],
            ':id'       => $user_data['id'],
        )
    );
    // $update_user_set->debugDumpParams();
    // exit;

    if ($update_user_result) {
        $_SESSION['user_level'] = $user_data['user_level'];
        redirect_to('index.php');
    } else {
        return 'Guess you got canned....';
    }
}

function isCurrentUserAdminAbove()
{
    return !empty($_SESSION['user_level']);
}

function isUsernameExists($username)
{
    $pdo = Database::getInstance()->getConnection();
    ## TODO: finish the following lines to check if there is another row in the tbl_user that has the given username
    $user_exists_query  = 'SELECT COUNT(*) FROM tbl_user WHERE user_name = :username';
    $user_exists_set    = $pdo->prepare($user_exists_query);
    $user_exists_result = $user_exists_set->execute(
        array(
            ':username' => $username,
        )
    );

    return !$user_exists_result || $user_exists_set->fetchColumn() > 0;
}
