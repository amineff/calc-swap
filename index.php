<!DOCTYPE HTML>
<html lang="en">
<head>
    <style>
        .error {color: #FF0000;}
    </style>
    <title>Calc Swap parameters</title>
</head>
<body>

<?php

require 'vendor/autoload.php';

use Carbon\Carbon;

// define variables and set to empty values
$durationErr = $prepaidErr = $paid_failedErr = "";
$duration = $prepaid = $paid_failed= 0;
$swap_date = Carbon::now()->format('Y-m-d');
$start_date = Carbon::now()->format('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $duration = $_POST["duration"];
    // check if name only contains letters and whitespace
    if (filter_var($duration, FILTER_VALIDATE_INT)  === false) {
        $durationErr = "Only numbers are allowed";
    }

    if(1 > $duration) {
        $durationErr = "Duration must be higher then zero";
    }

    $prepaid = $_POST["prepaid"];

    // check if name only contains letters and whitespace
    if (filter_var($prepaid, FILTER_VALIDATE_INT)  === false) {
        $prepaidErr = "Only numbers are allowed";
    }


    $frequency = $_POST["frequency"];
    $paid_failed = $_POST["paid_failed"];
    $start_date = $_POST["start_date"];
    $swap_date = $_POST["swap_date"];
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}



?>

<h2>Circuly subscription duration calculator</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    Duration: <input type="text" name="duration" value="<?php echo $duration;?>">
    <span class="error">* <?php echo $durationErr;?></span>
    <br><br>
    Prepaid: <input type="text" name="prepaid" value="<?php echo $prepaid;?>">
    <span class="error">* <?php echo $prepaidErr;?></span>
    <br><br>
    Paid / failed: <input type="text" name="paid_failed" value="<?php echo $paid_failed;?>">
    <span class="error"> <?php echo $paid_failedErr;?></span>
    <br><br>
    Start date: <input type="date" id="start_date" name="start_date" value="<?php echo $start_date;?>">
    <br><br>
    Swap date: <input type="date" id="swap_date" name="swap_date" value="<?php echo $swap_date;?>">
    <br><br>
    <label for="Subscription frequency">Subscription frequency:</label>
    <select name="frequency" id="frequency">
        <option value="monthly">monthly</option>
        <option value="weekly">weekly</option>
        <option value="daily">daily</option>
        <option value="yearly">yearly</option>
    </select>
    <br><br>
    <br><br>
    <input type="submit" name="Calc swap" value="Submit">
</form>


<?php

     function add_date_by_frequency($date, $frequency, $length = 1)
    {

        $period = '';
        switch (trim($frequency)) {
            case 'daily':
                $period = sprintf(' + %d days', $length);
                break;
            case 'weekly':
                $period = sprintf(' + %d weeks', $length);
                break;
            case 'monthly':
                $period = sprintf(' + %d months', $length);
                break;
            case 'yearly':
                $period = sprintf(' + %d years', $length);
                break;
        }

        return date('Y-m-d', strtotime($date . $period));
    }



if ($_SERVER["REQUEST_METHOD"] == "POST") {



    echo "<h2>Results:</h2>";
    echo 'Duration:'. $duration;
    echo str_repeat("<br>", 1);
    echo 'Prepaid:'. $prepaid;
    echo str_repeat("<br>", 1);


    $subscription_end = add_date_by_frequency($start_date, $frequency, $duration);


    echo "<br>";

    echo '------------------  Results  -----------------' . "<br>";


    echo 'subscription_start:' . $start_date . str_repeat("<br>", 1);
    echo 'subscription_end:' . $subscription_end . str_repeat("<br>", 2);

    echo "<h2>If swapped:</h2>";


    $subscription_duration  = ($duration - $prepaid) - $paid_failed;
    echo  'Original subscription_duration:'. $subscription_duration. str_repeat("<br>", 1);
    echo "<br>";


    $time = Carbon::make($swap_date)->diff($start_date);
    $diff_months = ($time->y ? $time->y * 12 : $time->m);
    echo "Diffrence between subscription_start date and swap date: months:{$diff_months} days:{$time->d}";
    echo "<br>";
    $time = Carbon::make($swap_date)->diff($subscription_end);
    $diff_months = ($time->y ? $time->y * 12 : $time->m);
    echo "Diffrence between subscription_end date and swap date: months:{$diff_months} days:{$time->d}";
    echo "<br>";


   // echo Carbon::now()->diffInMonths($current_date);
    //echo Carbon::make($current_date)->diffInMonths(Carbon::now());
    //echo Carbon::make($current_date)->diffInMonths($subscription_end);


    $diff_subscription_duration = match ($frequency) {
        'daily' =>  Carbon::make($swap_date)->startOfDay()->diffInDays($subscription_end),
        'weekly' =>  Carbon::make($swap_date)->startOfDay()->diffInWeeks($subscription_end),
        'monthly' =>  Carbon::make($swap_date)->startOfDay()->diffInMonths($subscription_end),
        'yearly' =>  Carbon::make($swap_date)->startOfDay()->diffInYears($subscription_end),
    };

    //echo  $settled;
    $subscription_duration1 = ($diff_subscription_duration - $paid_failed);
    $subscription_duration_prepaid1
        = max($prepaid
        - Carbon::make($swap_date)->startOfDay()->diffInMonths($start_date), 0);


    echo 'Subscription_duration_formula_calc_from_end_date:' . $subscription_duration1;
    echo "<br>";
    echo "<br>";

    echo 'Subscription_duration_prepaid:' . $subscription_duration_prepaid1;
    echo "<br>";
    echo "<br>";

    $diff_months = Carbon::make($swap_date)->diffInMonths($start_date);
    $another_subscription_duration = (($duration ) - $paid_failed) - $diff_months;
    echo 'Subscription_duration__formula_calc_from_start_date:' . $another_subscription_duration;

}
?>


</body>
</html>