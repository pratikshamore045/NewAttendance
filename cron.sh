#!/bin/bash

# Given URLs
urls=(
    "imeetattendance.ourlib.in"
    "sbmp.attendance.ourlib.in"
    "djsce.attendance.ourlib.in"
    "nmcce.attendance.ourlib.in"
    "nmhyd.attendance.ourlib.in"
    "mpst.attendance.ourlib.in"
    "bncp.attendance.ourlib.in"
    "sptm.attendance.ourlib.in"
    "upgcm.attendance.ourlib.in"
    "dertfootfall.ourlib.in"
    "pdalmiaattendance.ourlib.in"
    "bshattendance.ourlib.in"
    "vwesfootfall.ourlib.in"
    "arkattendance.ourlib.in"
    "attendancekohinoor.ourlib.in"
)

# Iterate through the URLs and call curl for each one
for url in "${urls[@]}"
do
    # Call autoOut.php endpoint using curl
    curl "http://$url/autoOut.php"

    echo "$url Get All out"
done

