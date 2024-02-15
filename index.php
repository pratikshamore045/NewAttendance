<?php
session_start();
if (isset($_SESSION['user_id'])) {
} else {
 header('Location: signin.php'); 
  exit();
}
include("User.class.php");
include("header.php");
?>

<div class="container-fluid p-0 position-relative rounded-bottom mb-2 ">
    <div class="text-center rounded-bottom " style="background: #483d8b;">
        <h1 class="display-4 text-white py-4">Footfall Tracking System</h1>
    </div>

    <!-- <div class="d-flex justify-content-end align-items-center"> -->
    <button type="button" class="btn btn-danger btn-custom position-absolute top-0 end-0 mt-1 mr-2 ">
        <a href="logout.php" style="color: #f2f2f2; text-decoration: none;">Log out </a><svg
            xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-box-arrow-right"
            viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z" />
            <path fill-rule="evenodd"
                d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
        </svg>
    </button>
    <!-- </div> -->
</div>
<div class="container ">
    <form method="POST" id="in_time_form" action="#">
        <div class="">
            <div class="row align-items-center w-100 p-2 rounded" style="background: rgba(119, 133, 255, 0.5);">
                <div class="col-md-2 text-center">
                    <label for="purpose" class="form-label m-0 fw-bold" style="font-size: 19px; color: #fff;">Purpose of
                        visit:</label>
                </div>
                <div class="col-md-3">
                    <select name="purpose" id="purpose" class="form-select">
                        <option value="Books Issuing">Books issuing</option>
                        <option value="E-Resources">E-Resources</option>
                        <option value="Internet Search">Internet search</option>
                        <option value="No dues Clearance">No dues Clearance</option>
                        <option value="Project Work">Project work</option>
                        <option value="Photos/pictures">Photos/pictures</option>
                        <option value="Periodicals Issuing">Periodicals Issuing</option>
                        <option value="Question Papers">Question Papers</option>
                        <option value="Reading Newspaper">Reading Newspaper</option>
                        <option value="Reference Books/Periodicals">Reference Books/Periodicals</option>
                        <option value="Study" selected>Study</option>
                    </select>
                </div>
                <div class="col-md-3 text-center ">
                    <label for="cardnumber" class="form-label m-0 fw-bold" style="font-size: 19px; color: #fff;">Enter
                        card number:</label>
                </div>
                <div class="col-md-2">
                    <input type="text" name="cardnumber" id="cardnumber" class="form-control" required
                        placeholder="Enter Card Number" autofocus>
                </div>
                <div class="col-md-2 text-center">
                <button class="btn btn-dark btn-block text-white custom-button" type="submit" value="IN/OUT" name="submit" id="submitButton">
                <span id="buttonText">IN / OUT</span>
                 <div id="loader" style="display: none;" class="spinner-border spinner-border spinner-border-sm" role="status">
                    <span class="sr-only "></span>
                </div>
                </button>


                </div>
            </div>
        </div>
    </form>
    <div class="w-100; p-0 ml-1">
        <div id="response"></div>
    </div>

    
    <div>
        <h2 class="mt-4 mb-4 text-center ">
            <strong>
<!--                <span class="blur-background  text-white" >List of 
		    user's in the library</span>-->
<?php

   $u = new User;
    $branchcode1 = $u->getBranchcode();
    echo '<span class="blur-background  text-white" >List of 
                    users in  </span><br><span class="blur-background  text-white" style="overflow: hidden;
                    display: -webkit-inline-box;
                    -webkit-line-clamp: 1; /* number of lines to show */
                            line-clamp: 1; 
                    -webkit-box-orient: vertical;" >' . $branchcode1 .'.</span>';

?>


            </strong>
        </h2>
    </div>
    <div id="tableDataFromRespons">
    <div class="row p-2 rounded" style="background: rgba(119, 133, 255, 0.5); ">
        <div class="col-sm-9">
            <?php
       // $u = new User;
        $in_members = $u->getInMembers();
        if (is_array($in_members) || is_object($in_members)) {
            $wordcount = count($in_members);
        }
        ?>
            <div class="table-responsive bg-white text-dark p-3 rounded my-3">
                <table id="inMembersTable" class="table text-dark  ">
                    <thead class="text-dark fw-bold mt-1">
                        <tr>
                            <th>Sr.No.</th>
                            <th>Card number</th>
                            <th>Name</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        <?php
                    $sr_no = 1;
                    if (is_array($in_members) || is_object($in_members)) {
                        foreach ($in_members as $in) {
                            if (empty($in['out_time'])) {
                                echo '<form method="POST" id="out_time_form" action="update_ajax.php">';
                                echo '<tr>';
                                echo '<td>' . $sr_no . '</td>';
                                echo '<td id="cr_no">' . $in["cardnumber"] . '</td>';
                                echo '<td style="text-transform: capitalize;"> ' . $in["firstname"] . " " . $in["surname"] . '</td>';
                                echo '<td>' . $in["purpose"] . '</td>';
                                echo '</tr>';
                                $sr_no++;
                                echo '<input type="hidden" id="cardnumber1" name="cardnumber1" value=' . $in["cardnumber"] . ' />';
                                echo '</form>';
                            }
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-3 my-3 rounded">
            <div class="w-100 text-center">
                <div id="currentTime" class="text-dark p-1 shadow-lg fs-4 bg-white border-bottom rounded"></div>
                <div id="currentDate" class="text-dark p-1 shadow-lg fs-4 bg-white rounded"></div>
                <hr>
                <div id="" class="text-dark p-1 shadow-lg fs-4 border-bottom bg-white rounded">Currenlty in
                    library<br>
                </div>
                <!-- <div class="fs-5 text-dark p-1 shadow-lg  bg-white rounded">
                    <?php echo($u->getInMembersCounts('out_time is null','')); ?>
                </div>  -->
                <div class="fs-5 text-dark p-1 shadow-lg  bg-white rounded">
                    <?php echo($u->getInMembersCounts('out_time is null','')); ?>
                </div>
                <hr>
                <div id="" class="text-dark p-1 shadow-lg fs-4 border-bottom bg-white rounded ">Today's footfall count
                    <br>
                </div>
                <div class="fs-5 text-dark p-1 shadow-lg  bg-white rounded ">
                    <?php echo($u->getInMembersCounts('DATE(in_time) = CURDATE()','')); ?>
                </div>
                <hr>
                <div id="" class="text-dark p-1 shadow-lg fs-4 border-bottom bg-white rounded">Total footfall count
                </div>
                <div class="fs-5 text-dark p-1 shadow-lg bg-white rounded mb-5 ">
                    <?php echo($u->getInMembersCounts('DATE(in_time) <= CURDATE()','')); ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<script>
// console.log(jQuery.fn.jquery);

$(document).ready(function() {
    $("#in_time_form").submit(function(event) {
        event.preventDefault();
        $("#buttonText").hide();
        $("#loader").show();
        //setTimeout(function() {
        var formData = {
            cardnumber: $("#cardnumber").val(),
            purpose: $("#purpose").val(),
            submit: "IN/OUT"
        };
        $.ajax({
            type: "POST",
            url: "update_ajax.php",
            data: formData,
            success: function(response) {
            // response = response.replace(/(\r\n|\n|\r)/gm, "");
            console.log(response);
            var returnResult = JSON.parse(response);
                if(returnResult['checkZero'] == '0'){
                    $("#response").html('<div class=" mt-3  rounded"><div class="alert py-2 alert-danger text-center text-xl" role="alert"><p class="fs-5 my-0 py-0">Invalid! CardNumber </p> </div></div>');
                    clearForm(); // Clear form inputs
                    focusOnFirstField();
                }
                else{
                    $("#response").html(returnResult['html']);
                    clearForm(); // Clear form inputs
                    focusOnFirstField();
                    $.ajax({
                        type: "GET",
                        url: "getData.php", 
                        dataType: "html",
                        success: function(response) {
                            $("#tableDataFromRespons").html(response);
                            
                             

                        },
                        error: function() {
                            alert("Error fetching HTML content");
                        }
                    });
                }

            },
            error: function() {
                $("#response").html("An error occurred.");
	    },
	    complete: function() {
		$("#loader").hide();
	        $("#buttonText").show();

	
	    }
        });
       // $("#loader").hide();
        //$("#buttonText").show();
        // }, 3000);
    });
});

$(document).ready(function() {
    new DataTable('#inMembersTable');
});
$(document).ready(function() {
    $('#inMembersTable').DataTable({
        "paging": true, // Enable pagination
        "pageLength": 10, // Number of records to show per page

    });
});


function updateCurrentTime() {
    var date = new Date(); // Get the current date and time

    // Get the day of the week and format it to display the full day name
    var daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var dayOfWeek = daysOfWeek[date.getDay()];

    var day = date.getDate();
    var month = date.getMonth() + 1; // Month is 0-based, so add 1
    var year = date.getFullYear();

    // Format the day, month, and year with leading zeros if needed
    var formattedDay = (day < 10) ? '0' + day : day;
    var formattedMonth = (month < 10) ? '0' + month : month;

    var formattedDate = `${dayOfWeek} ${formattedDay}-${formattedMonth}-${year}`; // Format the date as "Monday 25-09-2023"
    
    var hour = date.getHours();
    var minute = date.getMinutes();
    var second = date.getSeconds();
    var ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    hour = hour ? hour : 12;
    hour = hour < 10 ? '0' + hour : hour;
    minute = minute < 10 ? '0' + minute : minute;
    second = second < 10 ? '0' + second : second;
    var formattedTime = `${hour}:${minute}:${second} ${ampm}`;
    
    document.getElementById('currentDate').innerHTML = formattedDate;
    document.getElementById('currentTime').innerHTML = formattedTime;
}

setInterval(updateCurrentTime, 1000); // Update the time every second



function clearForm() {
    document.getElementById("cardnumber").value = "";
    // document.getElementById("purpose").value = "";
}

function focusOnFirstField() {
    document.getElementById("cardnumber").focus();
}

</script>


</div>
<footer id="sticky-footer" class="footer bg-dark text-white fixed-bottom">
    <div class="container text-center p-2">
        <span style="font-weight: bold;">Design and Developed by : </span>
        <a target="_blank" href="https://ourlib.in/" target="_new" class="text-info font-weight-bold">ourlib.in</a>
    </div>
</footer>
