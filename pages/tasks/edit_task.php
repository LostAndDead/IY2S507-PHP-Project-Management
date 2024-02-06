<?php
require "../../utils/sql.php";

session_start();

$loggedIn = false;
$uuid = "";
$permissionLevel = 0;
$method = "";
$taskUUID = "";

if(!empty($_SESSION["uuid"])){
    $loggedIn = true;
    $uuid = $_SESSION["uuid"];
    $permissionLevel = getPermissionLevel($uuid);
}

if(empty($_SESSION["uuid"])){
    header("Location: ./login.php");
}

$title = $content = $status = $date = $msg = "";
$titleErr = $contentErr = $statusErr = $dateErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["method"])){
        $method = "create";
    } else {
        switch ($_POST["method"]){
            case "edit": {
                $method = "edit";
                if(!empty($_POST["uuid"])){
                    $taskUUID = $_POST["uuid"];
                }
                break;
            }
            default: {
                $method = "create";
                break;
            }
        }
    }

    if (empty($_POST["title"])) {
        $titleErr = "Title is required";
    } else {
        $title = test_input($_POST["title"]);
    }

    if (empty($_POST["content"])) {
        $contentErr = "Content is required";
    } else {
        $content = test_input($_POST["content"]);
    }

    if (empty($_POST["status"])) {
        $statusErr = "Status is required";
    } else {
        $status = test_input($_POST["status"]);
    }

    if (empty($_POST["date"])) {
        $dateErr = "Date is required";
    } else {
        $date = test_input($_POST["date"]);
        if(!strtotime($date)){
            $dateErr = "Invalid Date";
        }
    }

    if(!$titleErr && !$contentErr && ! $statusErr && !$dateErr){
        if($method == "create"){
            $statusID = statusStrToInt($status);
            $completionDate = strtotime($date);
            $result = createTask($title, $content, $statusID, $completionDate, $uuid);

            if($result){
                $msg = "Task created, redirecting to homepage...";
                header( "refresh:3;url=../homepage.php");
            }else{
                $msg = "An error occurred, please try again.";
            }
        }else if ($method == "edit"){
            $statusID = statusStrToInt($status);
            $completionDate = strtotime($date);
            $result = updateTask($taskUUID, $title, $content, $statusID, $completionDate, $uuid);

            if($result){
                $msg = "Task updated, redirecting to homepage...";
                header( "refresh:3;url=../homepage.php");
            }else{
                $msg = "An error occurred, please try again.";
            }
        }
    }
} else if($_SERVER["REQUEST_METHOD"] == "GET") {
    if (empty($_GET["method"])){
        $method = "create";
    } else {
        switch ($_GET["method"]){
            case "edit": {
                $method = "edit";
                if(!empty($_GET["uuid"])){
                    $taskUUID = $_GET["uuid"];
                }
                $task = getTask($taskUUID, $uuid);
                if(!$task){
                    $method = "create";
                } else {
                    $title = $task[$taskUUID]["title"];
                    $content = $task[$taskUUID]["content"];
                    $status = statusIntToStr($task[$taskUUID]["status"]);
                    $date = $task[$taskUUID]["completion_date"];
                }

                break;
            }
            default: {
                $method = "create";
                break;
            }
        }
    }

}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data);
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">

    <?php
    switch ($method){
        case "create": {
            echo '<title>Create Task</title>';
            break;
        }
        case "edit": {
            echo '<title>Edit Task</title>';
            break;
        }
    }
    ?>
    <link rel="stylesheet" href="../../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../homepage.php">Home</a>
        <?php
        if($permissionLevel == 2){
            echo '<a class="navbar-brand" href="../manage.php">Manage</a>';
        }
        ?>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../password_reset.php">Change Password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5 h-75">
    <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card bg-dark text-white" style="border-radius: 1rem;">
                <div class="card-body p-3 text-center">
                    <div class="mb-md-5 mt-md-4 pb-5">
                        <?php
                        switch ($method){
                            case "create": {
                                echo '<h2 class="fw-bold mb-2 text-uppercase">Create Task</h2>';
                                echo '<p class="text-white-50 mb-5">Enter the details to create your task.</p>';
                                break;
                            }
                            case "edit": {
                                echo '<h2 class="fw-bold mb-2 text-uppercase">Edit Task</h2>';
                                echo '<p class="text-white-50 mb-5">Edit the task details bellow.</p>';
                                break;
                            }
                        }
                        ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <div class="form-outline form-white form-floating mb-4">
                                <input type="text" id="typeTitleX" name="title" class="form-control form-control-lg" placeholder="title" value="<?php echo $title;?>"/>
                                <?php
                                if($titleErr){
                                    echo '<label class="form-label-error" for="typeTitleX">' . $titleErr . '</label>';
                                }else {
                                    echo '<label class="form-label" for="typeTitleX">Task Title</label>';
                                }
                                ?>
                            </div>
                            <div class="form-outline form-white form-floating mb-4">
                                <textarea style="height: 150px" type="text" id="typeContentX" name="content" class="form-control form-control-lg" placeholder="content""/><?php echo $content;?></textarea>
                                <?php
                                if($contentErr){
                                    echo '<label class="form-label-error" for="typeContentX">' . $contentErr . '</label>';
                                }else {
                                    echo '<label class="form-label" for="typeContentX">Content</label>';
                                }
                                ?>
                            </div>
                            <div class="form-outline form-white form-floating mb-4">
                                <select class="form-select form-control-lg" id="typeStatusX" name="status">

                                    <?php
                                    switch ($status){
                                        default:{
                                            echo '<option selected>Not started</option>';
                                            echo '<option>Started</option>';
                                            echo '<option>On hold</option>';
                                            echo '<option>Completed</option>';
                                            break;
                                        }
                                        case "Started":{
                                            echo '<option>Not started</option>';
                                            echo '<option selected>Started</option>';
                                            echo '<option>On hold</option>';
                                            echo '<option>Completed</option>';
                                            break;
                                        }
                                        case "On hold":{
                                            echo '<option>Not started</option>';
                                            echo '<option>Started</option>';
                                            echo '<option selected>On hold</option>';
                                            echo '<option>Completed</option>';
                                            break;
                                        }
                                        case "Completed":{
                                            echo '<option>Not started</option>';
                                            echo '<option>Started</option>';
                                            echo '<option>On hold</option>';
                                            echo '<option selected>Completed</option>';
                                            break;
                                        }
                                    }
                                    ?>
                                </select>
                                <?php
                                if($statusErr){
                                    echo '<label class="form-label-error" for="typeStatusX">' . $statusErr . '</label>';
                                }else {
                                    echo '<label class="form-label" for="typeStatusX">Status</label>';
                                }
                                ?>
                            </div>
                            <div class="form-outline form-white form-floating mb-4">
                                <input type="date" id="typeDateX" name="date" class="form-control form-control-lg" placeholder="date" value="<?php echo $date;?>"/>
                                <?php
                                if($dateErr){
                                    echo '<label class="form-label-error" for="typeDateX">' . $dateErr . '</label>';
                                }else {
                                    echo '<label class="form-label" for="typeDateX">Completion Date</label>';
                                }
                                ?>
                            </div>

                            <?php
                                if($method == "edit"){
                                    echo '<input type="hidden" name="method" value="edit">';
                                    echo '<input type="hidden" name="uuid" value="' . $taskUUID . '">';
                                }
                            ?>
                            <?php
                            if($msg){
                                echo '<div>';
                                echo '<p class="mb-0">' . $msg . '</p>';
                                echo '<div>';
                            }
                            ?>
                            <?php
                                switch ($method){
                                    case "create": {
                                        echo '<button class="btn btn-outline-light btn-lg px-5" type="submit" name="submit">Create</button>';
                                        break;
                                    }
                                    case "edit": {
                                        echo '<button class="btn btn-outline-light btn-lg px-5" type="submit" name="submit">Update</button>';
                                        break;
                                    }
                                }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>