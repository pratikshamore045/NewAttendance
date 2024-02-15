<?php
session_start();
######
include("User.class.php");
include("header.php");
$u=new User();
// if($_POST)
// {
// if(!empty($_POST['username']) &&  $u->authenticate($_POST['username'],$_POST['password'])){
// $_SESSION['user_id']=$u->user_id;

// 		header("location:index.php");
// 		exit;
//                 }
//                 else{
//             //    echo "<script> alert('Wrong Credentials, please try again!');
               
//             //    </script>";

//             echo "<script> alert('Wrong Credentials, please check Username,Password and Categorycode...!');               
// 		</script>";
//                 }
// }


if ($_POST) {
    $result = $u->authenticate($_POST['username'], $_POST['password']);

    if ($result === true) {
        // Authentication successful
        $_SESSION['user_id'] = $u->user_id;
        header("location: index.php");
        exit;
    }else {
        // Print the value to the console using JavaScript
        // echo "<script>console.log('Authentication Result: $result');</script>";

         $errorMessage = '';
        switch ($result) {
            case 'wrong_category':
                $errorMessage = 'Your category is not Staff. Please check the same in koha and update.';
                break;
            default:
                $errorMessage = 'Incorrect credentials, please check Username and Password. ';
                break;
        }

        echo "<script>alert('$errorMessage');</script>";
    }
}
?>
    <!-- Modal modal-lg modal-dialog-centered   modal-dialog-scrollable-->
    <div class="modal fade" id="loginModel" tabindex="-1" data-bs-backdrop="static" aria-labelledby="loginModelLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content" style="background-color: inherit;">
                <div class="modal-body">
                    <div class="container pt-3 pb-3">
                     
                        <div class="card shadow rounded mx-auto" >
                            <div class="card-header h4 " style="background: #483d8b; color: white; padding: 15px;"><i class="bi bi-person-circle  "></i> Login </div>
                            <div class="card-body">
                                <form action="" method="post" id="loginForm" class="row g-3">
                                    <div class="col-12">
                                        <input type="hidden" name="form_action" value="signin">
                                        <label for="username" class="form-label"><i class="bi bi-person"></i>&nbsp; Username</label>
                                        <input required type="text" class="form-control" id="username" name="username" placeholder="Enter username.">
                                    </div>
                                    <div class="col-12">
                                        <label for="password" class="form-label"><i class="bi bi-lock"></i>&nbsp; Password</label>
                                        <input required type="password" class="form-control" id="password" name="password" placeholder="Enter Password">
                                    </div>
                                    <div class="col-12 d-inline-flex  justify-content-center">    
                                    <button type="submit"name="submit"value="submit" class="btn btn-custom">Log In</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
  
    
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('loginModel'), {})
        myModal.show()

    </script>

<style>
    .card {
        border-radius: 10px;
}
.btn-custom {
    background-color: #483d8b;
    color: white; /* Optionally, set the text color to white for better visibility on the dark background */
}
     

    </style>
