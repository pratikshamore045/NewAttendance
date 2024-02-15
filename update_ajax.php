<?php
include("User.class.php");
$u=new User;
if (isset($_POST['submit'])) {
        $_POST['cardnumber'] = trim($_POST['cardnumber']);
        if($u->checkPatronPresent($_POST['cardnumber']) == 1){
            if ($_POST['submit'] == 'IN/OUT' && !empty($_POST['cardnumber'])) {
                $in_cardnumber1 = $u->checkIn($_POST['cardnumber']);
                if ($in_cardnumber1 == "") {
                    $in_cardnumber = $u->insertCardnumber($_POST['cardnumber'], $_POST['purpose']);
                    if ($in_cardnumber == 'IN') {
                        $card=$u->getInMembersCounts('',$_POST["cardnumber"]);
                        $response = array( 'html' => '<div class=" mt-3 rounded">
                                        <div class="alert py-2 alert-success text-center text-xl" role="alert">
                                            <p class="fs-5 my-0 py-0" >Welcome <b>'. $card.'</b>, to the Library. 📚</p>
                                        </div>
                                        </div>',
                                        'checkZero' => 1,
                                );
                                echo json_encode($response);
                                    exit();
                    } else {
                        $Status = "error";
                    }
                } 
                else {
                    $out = $u->getOutMembers($_POST['cardnumber'],$_POST['purpose']);
                    if ($out == 'OUT') {
                        $Status = "OUT";
                    } else {
                        $Status = "error";
                    }
                    if ($out == 'OUT1') {
                        $Status1 = "OUT1";
                    } else {
                        $Status1 = "error";
                    }
                    if (isset($Status)) {
                        if ($Status != 'error') {
                            if ($Status == "OUT") {
                                $card=$u->getInMembersCounts('',$_POST["cardnumber"]);
                                
                                $response = array( 'html' => '<div class=" mt-3  rounded">
                                <div class="alert py-2 alert-warning text-center text-xl" role="alert">
                                    <p class="fs-5 my-0 py-0">Thank you, <b>'. $card.',</b> for visiting the library. 📚 </p>                                                                  
                                </div>
                            </div>',
                                 'checkZero' => 1,
                            );
                            echo json_encode($response);
                                
                                // echo '<div class=" mt-3  rounded">
                                //         <div class="alert py-2 alert-danger text-center text-xl" role="alert">
                                //             <p class="fs-5 my-0 py-0">"Thank you, <b>'. $_POST['cardnumber'].'!</b> for visiting the library. 📚" </p>                                                                  
                                //         </div>
                                //     </div>';
                                    exit();
                            } else {
                                $Status = "error";
                            }
                        }
                    }
                    if (isset($Status1)) {
                        if ($Status1 != 'error') {
                            if ($Status1 == "OUT1") {
                                $card=$u->getInMembersCounts('',$_POST["cardnumber"]);
                                
                                $response = array( 'html' => '<div class=" mt-3  rounded">
                                <div class="alert py-2 alert-success text-center text-xl" role="alert">
                                            <p class="fs-5 my-0 py-0" >Welcome <b>'. $card.'</b>, to the Library. 📚</p>
                                    </div>
                            </div>',
                                 'checkZero' => 1,
                            );
                            echo json_encode($response);
                                
                                // echo '<div class=" mt-3  rounded">
                                //         <div class="alert py-2 alert-danger text-center text-xl" role="alert">
                                //             <p class="fs-5 my-0 py-0">"Thank you, <b>'. $_POST['cardnumber'].'!</b> for visiting the library. 📚" </p>                                                                  
                                //         </div>
                                //     </div>';
                                    exit();
                            } else {
                                $Status1 = "error";
                            }
                        }
                    }
                }
            }
            // $response = array(
            //     'checkZero' => 1,
            // );
            // echo json_encode($response);
        }
        else{
            $response = array(
                'checkZero' => 0,
            );
            echo json_encode($response);
        }    
    }

 ?>
