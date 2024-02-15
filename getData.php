<?php 
include("User.class.php");
$u=new User;
    if (isset($_GET)) {
        // print_r ($_GET);
        echo `
        <div class="row p-2 rounded" style="background: rgba(119, 133, 255, 0.5); ">
        <div class="col-sm-9">`;
        $in_members = $u->getInMembers();
        if (is_array($in_members) || is_object($in_members)) {
            $wordcount = count($in_members);
        }
            echo '<div class="row p-2 rounded" style="background: rgba(119, 133, 255, 0.5); ">
            <div class="col-sm-9">
            <div class="table-responsive bg-white text-dark p-3 rounded my-3">
                <table id="inMembersTable1" class="table text-dark  ">
                    <thead class="text-dark fw-bold mt-1">
                        <tr>
                            <th>Sr.No.</th>
                            <th>Card number</th>
                            <th>Name</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">';
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
                    echo '</tbody>
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
                <div class="fs-5 text-dark p-1 shadow-lg  bg-white rounded">
                    #';
                     echo($u->getInMembersCounts('out_time is null',''));
               echo '</div>
                <hr>
                <div id="" class="text-dark p-1 shadow-lg fs-4 border-bottom bg-white rounded ">Today\'s footfall count
                    <br>
                </div>
                <div class="fs-5 text-dark p-1 shadow-lg  bg-white rounded ">
                    #';
                    echo($u->getInMembersCounts('DATE(in_time) = CURDATE()',''));
                echo '</div>
                <hr>
                <div id="" class="text-dark p-1 shadow-lg fs-4 border-bottom bg-white rounded">Total footfall count
                </div>
                <div class="fs-5 text-dark p-1 shadow-lg bg-white rounded ">
                    #';
                 echo($u->getInMembersCounts('DATE(in_time) <= CURDATE()',''));
               echo' </div>
            </div>
        </div>
    </div>
    
    <script>
$(document).ready(function() {
    new DataTable("#inMembersTable1");
});
$(document).ready(function() {
    $("#inMembersTable1").DataTable({
        "paging": true, 
        "pageLength": 10, 
    });
});
</script>';
    }


?>

