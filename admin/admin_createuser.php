<?php
require_once '../load.php';
confirm_logged_in(true);

if (isset($_POST['submit'])) {
    $data = array(
        'fname'      => trim($_POST['fname']),
        'username'   => trim($_POST['username']),
        'password'   => trim($_POST['password']),
        'email'      => trim($_POST['email']),
        'user_level' => trim($_POST['user_level']),
    );

    $message = createUser($data);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
</head>

<body>
    <h2>Create User</h2>
    <?php echo !empty($message) ? $message : ''; ?>
    <form action="admin_createuser.php" method="post">
        <label for="first_name">First Name</label>
        <input id="first_name" type="text" name="fname" value=""><br><br>

        <label for="username">Username</label>
        <input id="username" type="text" name="username" value=""><br><br>

        <label for="password">Password</label>
        <input id="password" type="text" name="password" value=""><br><br>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value=""><br><br>

        <label for="user_level">User Level</label>
        <select id="user_level" name="user_level">
            <?php $user_level_map = getUserLevelMap();
foreach ($user_level_map as $val => $label): ?>
            <option value="<?php echo $val; ?>"><?php echo $label; ?>
            </option>
            <?php endforeach;?>
        </select><br><br>

        <button type="submit" name="submit">Create User</button>
    </form>
</body>

</html>