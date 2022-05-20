<?php
header("Content-Type: application/json, plain/text");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST,GET");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization");
require_once"../assets/config/db.inc.php";
require_once"../assets/config/response.php";



if(isset($_GET['type'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['type'] === 'login') {
        loginUser($con);
    }
    else
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['type'] === 'register' && isset($_GET['Utype'])) {
        registerUser($con);
    }
    else
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['type'] === 'upload' && isset($_GET['user'],$_GET['pass'])) {
         uploadresume($con);
    }
    else
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['type'] === 'updateaddress' && isset($_GET['user'],$_GET['pass'])) {
        updateaddress($con);
    }
    else
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['type'] === 'updateuser' && isset($_GET['user'],$_GET['pass'])) {
        updateUser($con);
    }
    else
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'deactivate' && isset($_GET['user'],$_GET['pass'],$_GET['user'])) {
        deactivate($con);
    }
}

function registerUser(mysqli $con)
{

    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $Utype = $_GET['Utype'];
    $Address = $_POST['cAddress'];
    $password = hash('sha256', $_POST['Pwd']);
    $rpassword = hash('sha256', $_POST['rPwd']);
    $token = sha1(uniqid(mt_rand(), true));

    if (empty($Utype)) {
        response(401, "Unauthorized", "Type not indicated!!!");
        die();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        response(400, "Bad Request", "bad email address");
        die();
    }

    if ($password !== $rpassword) {
        response(400, "Bad Request", "passwords inconsistent");
        die();
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    $type = "Insert into user_type (user_type,token) values (?,?);";
    $stmt = $con->stmt_init();
    if (!$stmt->prepare($type)) {
        response(400, "Something went wrong", "At register fun() line 53");
        die;
    }
    $stmt->bind_param('ss', $Utype, $token);

    if ($stmt->execute()) {
        if ($stmt->affected_rows) {
            $id = $stmt->insert_id;
            if ($Utype == '1') {

                $fName = $_POST['fName'];
                $lName = $_POST['lName'];
                $BOD = $_POST['DOB'];

                if (empty($fName) || empty($lName) || empty($BOD) ||empty($email) || empty($mobile) || empty($password) || empty($rpassword) || empty($Address)) {
                    response(401, "Unauthorized", "All fields need to be filled!!!");
                    die();
                }

                $userClient = "Insert into userClient_details (id,token, F_Name, L_Name, BOD, email, mobile, password, address) values (?,?,?,?,?,?,?,?,?);";
                $stmt = $con->stmt_init();
                if (!$stmt->prepare($userClient)) {
                    response(400, "Something went wrong", "At register fun() line 86");
                    die;
                }
                $stmt->bind_param('sssssssss', $id, $token, $fName, $lName, $BOD, $email, $mobile, $password, $Address);
                if($stmt->execute()) {

                    if ($stmt->affected_rows) {
                        // *****************last ID **********************
                        $id = $stmt->insert_id;
                        $key = base64_encode($id);
                        $id = $key;
                        $site = $_SERVER['HTTP_HOST'];
                        $message = "					
                        Hello $fName,
                        <br /><br />
                        Welcome to +233 Recruitment!<br/>
                        Confirm your Details <br/>
                        Email: $email
                        <br/>
                        mobile: $mobile 
                        <br />
                        And to complete your registration,  please just click following link<br/>
                        <br /><br />Click<a href='http://$site/index.php?type=verify&id=$id&code=$token'> HERE  </a>to Activate
                        <br /><br />
                        Thanks";
                        $subject = "Confirm Registration";

                        try {
                            send_mail($email, $message, $subject);
                        } catch (phpmailerException $e) {
                        }
                        response(200, "OK", "Congratulation!!\n Registration successful");
                        die();
                    }
                }
                }

            else
                if ($Utype == '2') {
                    $cName = $_POST['cName'];

                    $cdescription = $_POST['cdescription'];
                    $ctax = $_POST['ctax'];

                    if (empty($cName) || empty($Address) || empty($email) || empty($mobile) || empty($cdescription) || empty($ctax) || empty($password) || empty($rpassword)) {
                        response(401, "Unauthorized", "All fields need to be filled!!!");
                        die();
                    }
                    $user = "Insert into usercompany_details (id, token, cName, description, Tax_id, password, email, mobile, address) values (?,?,?,?,?,?,?,?,?);";
                    $stmt = $con->stmt_init();
                    if (!$stmt->prepare($user)) {
                        response(400, "Something went wrong", "At register fun() line 84");
                        die;
                    }
                    $stmt->bind_param('sssssssss', $id, $token, $cName, $cdescription, $ctax, $password, $email, $mobile, $cAddress);
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows) {
                            // *****************last ID **********************
                            $id = $stmt->insert_id;
                            $key = base64_encode($id);
                            $id = $key;
                            $site = $_SERVER['HTTP_HOST'];
                            $message = "					
                        Hello $cName,
                        <br /><br />
                        Welcome to +233 Recruitment!<br/>
                        Confirm your Details <br/>
                        Email: $email
                        <br/>
                        mobile: $mobile 
                        <br />
                        And to complete your registration,  please just click following link<br/>
                        <br /><br />Click<a href='http://$site/index.php?type=verify&id=$id&code=$token'> HERE  </a>to Activate
                        <br /><br />
                        Thanks";
                            $subject = "Confirm Registration";

                            try {
                                send_mail($email, $message, $subject);
                            } catch (phpmailerException $e) {
                            }
                        }
                        response(200, "OK", "Congratulation!!\n Registration successful");
                        die();
                    }

                } else {
                    response(400, "error", "Type is not active");
                    die();
                }
            } else {
                response(400, "Something went wrong", "At register fun() line 100");
                die;
            }
        } else {
            response(400, "Something went wrong", "At register fun() line 105");
            die;
        }


}

function loginUser(mysqli $con){
    $Utype = $_POST['Utype'];
    $username = $_POST['userName'];
    $pwd = hash('sha256', $_POST['Pwd']);

    if($Utype == '1') {

        $sql = "select id,token,password,F_Name,L_Name from userclient_details where email=?;";
        $stmt = $con->stmt_init();
        if (!$stmt->prepare($sql)) {
            response(400, "Something went wrong", "At login fun() line 62");
            die;
        }
        $stmt->bind_param('s', $username);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            $type = "select user_type,status from user_type where token=?;";
            $stmt = $con->stmt_init();
            if (!$stmt->prepare($type)) {
                response(400, "Something went wrong", "At login fun() line 62");
                die;
            }
            $stmt->bind_param('s', $data['token']);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $status = $user['status'];
                if($status === 'Y') {
                    $isValid = password_verify($pwd, $data['password']);
                    if ($isValid) {
                        session_start();
                        // store user data in cookie
                        setcookie('user', json_encode([
                            'id' => $data['token'],
                            'username' => $username,
                            'password' => $pwd
                        ]), time() + 3600 * 24 * 30);
                        $_SESSION['type'] = '1';
                        $_SESSION['Fuser'] = $data['F_Name'];
                        $_SESSION['Luser'] = $data['L_Name'];
                        $_SESSION['id'] = $data['id'];
                        $_SESSION['Acc'] = base64_encode($data['token']);
                        $_SESSION['pass'] = base64_encode($pwd);
                        $_SERVER['PHP_AUTH_USER'] = $data['token'];
                        $_SERVER['PHP_AUTH_PW'] = $pwd;
                        response(200, "OK", "welcome " . $_SESSION['user']);
                    }
                }
                else{
                    response(200, "OK", "Please Activate or Reactivate to login" );
                }

            } else {
                response(400, "OK", "welcome " . $_SESSION['user']);
            }
            die();
        }
    }
    elseif($Utype == '2'){
    $sql = "select id,cName,token,password from usercompany_details where email=?;";
    $stmt = $con->stmt_init();
    if (!$stmt->prepare($sql)) {
        response(400, "Something went wrong", "At login fun() line 62");
        die;
    }
    $stmt->bind_param('s', $username);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $type = "select user_type,status from user_type where token=?;";
        $stmt = $con->stmt_init();
        if (!$stmt->prepare($type)) {
            response(400, "Something went wrong", "At login fun() line 62");
            die;
        }
        $stmt->bind_param('s', $data['token']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $status = $user['status'];
            if($status === 'Y') {
                $isValid = password_verify($pwd, $data['password']);
                if ($isValid) {
                    session_start();
                    // store user data in cookie
                    setcookie('user', json_encode([
                        'id' => $data['token'],
                        'username' => $username,
                        'password' => $pwd
                    ]), time() + 3600 * 24 * 30);
                    $_SESSION['type'] = '2';
                    $_SESSION['user'] = $data['cName'];
                    $_SESSION['id'] = $data['id'];
                    $_SESSION['Acc'] = base64_encode($data['token']);
                    $_SESSION['pass'] = base64_encode($pwd);
                    $_SERVER['PHP_AUTH_USER'] = $data['token'];
                    $_SERVER['PHP_AUTH_PW'] = $pwd;
                    response(200, "OK", "welcome " . $_SESSION['user']);
                    die();
                }
            }
            else{
                response(200, "OK", "Please Activate or Reactivate to login" );
            }
        } else {
            response(400, "OK", "welcome " . $_SESSION['user']);
        }
        die();
    }
    }
}

function uploadresume(mysqli $con)
{
    $token = $_GET['user'];
    $pass = $_GET['pass'];
    $pname = mt_rand(1000, 10000) . "-" . $_FILES["file"]["name"];
    $tname = $_FILES["files"]["tmp_name"];
    $uploads_dir = '../../assets/files/resumes';
    $sql = "select id,user_type from user_type where token=?;";
    $stmt = $con->stmt_init();
    if (!$stmt->prepare($sql)) {
        response(400, "Something went wrong", "At resume fun() line 203");
        die;
    }
    $stmt->bind_param('s', $token);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data['user_type'] == '1') {
            move_uploaded_file($tname, $uploads_dir . '/' . $pname);
            $upload = "INSERT into resumes(token,resume_name) VALUES('$token','$pname')";

            if (mysqli_query($con, $upload)) {

                echo "File Successfully uploaded";
            } else {
                echo "Error";
            }
        }
        else{
    echo "Not Allowed";
        }
    }
}

function updateaddress(mysqli $con)
{
    $token = $_GET['user'];
    $pass = $_GET['pass'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $type = "select id,user_type from user_type where token=?;";
    $stmt = $con->stmt_init();
    if (!$stmt->prepare($type)) {
        response(400, "Something went wrong", "At resume fun() line 203");
        die;
    }
    $stmt->bind_param('s', $token);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $Utype = $data['user_type'];

        if($Utype == '1'){
            $updater = "UPDATE userclient_details SET address=?, mobile=? WHERE token=?;";
            $stmt = $con->stmt_init();
            if (!$stmt->prepare($updater)) {
                response(400, "Something went wrong", "At Update fun() line 388");
                die;
            }
            $stmt->bind_param('sss', $address,$mobile, $token);
            if($stmt->execute()){
                response(200,"OK","Done");
                die();
            }
        }
        elseif($Utype == '2'){
            $updater = "UPDATE usercompany_details SET address=?, mobile=? WHERE token=?;";
            $stmt = $con->stmt_init();
            if (!$stmt->prepare($updater)) {
                response(400, "Something went wrong", "At Update fun() line 268");
                die;
            }
            $stmt->bind_param('sss', $address,$mobile, $token);
            if($stmt->execute()){
                response(200,"OK","Done");
                die();
            }
        }
        else{
            response(400, "Something went wrong", "At update fun() line 256");
            die;
        }
    }
}

function updateUser(mysqli $con){
    $token = $_GET['user'];
    $pass = $_GET['pass'];

    $email = $_POST['email'];
    $type = "select id,user_type from user_type where token=?;";
    $stmt = $con->stmt_init();
    if (!$stmt->prepare($type)) {
        response(400, "Something went wrong", "At resume fun() line 203");
        die;
    }
    $stmt->bind_param('s', $token);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $Utype = $data['user_type'];

        if($Utype == '1'){
            $fName = $_POST['fname'];
            $lName = $_POST['lname'];
            $BOD = $_POST['BOD'];
            $updater = "UPDATE userclient_details SET F_Name=?, L_Name=?, BOD=?, email=? WHERE token=?;";
            $stmt = $con->stmt_init();
            if (!$stmt->prepare($updater)) {
                response(400, "Something went wrong", "At Update fun() line 388");
                die;
            }
            $stmt->bind_param('sssss', $fName,$lName,$BOD,$email, $token);
            if($stmt->execute()){
                response(200,"OK","Done");
                die();
            }
        }
        elseif($Utype == '2'){
            $cDescription = $_POST['cDescription'];
            $cName = $_POST['cName'];
            $updater = "UPDATE usercompany_details SET cName=?, description=?, email=? WHERE token=?;";
            $stmt = $con->stmt_init();
            if (!$stmt->prepare($updater)) {
                response(400, "Something went wrong", "At Update fun() line 268");
                die;
            }
            $stmt->bind_param('ssss', $cName, $cDescription, $email, $token);
            if($stmt->execute()){
                response(200,"OK","Done");
                die();
            }
        }
        else{
            response(400, "Something went wrong", "At update fun() line 256");
            die;
        }
    }
}

function deactivate(mysqli $con){
    $token = $_GET['user'];
    $pass = $_GET['pass'];
    $fName = $_GET['name'];
    $email = $_GET['email'];
    $statusN = 'N';
    $type = "UPDATE user_type SET status=? where token=? ";
    $stmt = $con->stmt_init();
    if (!$stmt->prepare($type)) {
        response(400, "Something went wrong", "At resume fun() line 203");
        die;
    }
    $stmt->bind_param('ss', $statusN,$token);
    if ($stmt->execute()) {
        if ($stmt->affected_rows) {
            // *****************last ID **********************
            $id = $_SESSION['id'];
            $key = base64_encode($id);
            $id = $key;
            $site = $_SERVER['HTTP_HOST'];
            $message = "					
                        Hello $fName,
                        <br /><br />
                        Welcome to +233 Recruitment!<br/>
                        Account Deactivated <br/>
                       
                        <br />
                        if it is not done by you then to reverse,  please just click following link<br/>
                        <br /><br />Click<a href='http://$site/index.php?type=verify&id=$id&code=$token'> HERE  </a>to Reactivate
                        <br /><br />
                        Thanks";
            $subject = "Account Deactivated";

            try {
                send_mail($email, $message, $subject);
            } catch (phpmailerException $e) {
            }
        }
        response(200,"OK","Done");
        die();

        }

    response(400, "Something went wrong", "At update fun() line 256");
    die;

}

//send_mail
function send_mail($email, $message, $subject)
{
    require_once('../assets/mailer/PHPMailerAutoload.php');
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "ssl";
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465;
    $mail->AddAddress($email);
    $mail->Username = "tadisdoctor@gmail.com";
    $mail->Password = "H@!?/!+-&$#@ 00000009ijfg";
    $mail->SetFrom('tadisdoctor@gmail.com', '+233-recruitment');
    $mail->AddReplyTo("tadisdoctor@gmail.com", "+233-recruitment");
    $mail->Subject = $subject;
    $mail->MsgHTML($message);
    $mail->Send();
}

function accepted(){}