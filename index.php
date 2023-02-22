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
$durationErr = $prepaidErr = $paid_failedErr = $formula = $swapDateErr = $subscriptionSwapDateErr = "";
$duration = $prepaid = $paid_failed = 0;
$divide_rp = true;
$swap_date = Carbon::now()->addDays(1)->format('Y-m-d');
$start_date = Carbon::now()->format('Y-m-d');
$swap_subscription_start_date = Carbon::now()->format('Y-m-d');

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
    $formula = $_POST["formula"];
    $paid_failed = $_POST["paid_failed"];
    $start_date = $_POST["start_date"];
    $swap_subscription_start_date = $_POST["swap_subscription_start_date"];
    $swap_date = $_POST["swap_date"];
    $subscription_end = add_date_by_frequency($start_date, $frequency, $duration);
    $divide_rp = match ($formula) {
        'oldest'  =>  false,
        default =>  $_POST["divide_rp"] ?? false
    };

    if(Carbon::make($swap_date)->lte($start_date)) {
        $swapDateErr = "Swap dates has to be greater than the start date!";
    }

    if(Carbon::make($swap_date)->gt($subscription_end)) {
        $swapDateErr = "Swap dates exceeds the end date";
    }

    if(Carbon::make($swap_subscription_start_date)->lte($swap_date)) {
        $subscriptionSwapDateErr = "Swap subscription start date to be greater than the swap date!";
    }
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
    Paid / failed RPs: <input type="text" name="paid_failed" value="<?php echo $paid_failed;?>">
    <span class="error"> <?php echo $paid_failedErr;?></span>
    <br><br>
    Start date: <input type="date" id="start_date" name="start_date" value="<?php echo $start_date;?>">
    <br><br>
    Swap date: <input type="date" id="swap_date" name="swap_date" value="<?php echo $swap_date;?>">
    <span class="error"> <?php echo $swapDateErr;?></span>
    <br><br>
    Swap subscription (start date): <input type="date" id="swap_subscription_start_date" name="swap_subscription_start_date" value="<?php echo $swap_subscription_start_date;?>">
    <span class="error"> <?php echo $subscriptionSwapDateErr;?></span>
    <br><br>
    <label for="Subscription frequency">Subscription frequency:</label>
    <select name="frequency" id="frequency">
        <option <?php if($frequency == 'monthly'){echo("selected");}?> value="monthly">monthly</option>
        <option <?php if($frequency == 'weekly'){echo("selected");}?> value="weekly">weekly</option>
        <option <?php if($frequency == 'daily'){echo("selected");}?> value="daily">daily</option>
        <option <?php if($frequency == 'yearly'){echo("selected");}?> value="yearly">yearly</option>
    </select>
    <br><br>
    <label for="Formulas">Formula:</label>
    <select name="formula" id="formula">
        <option <?php if($formula == 'oldest'){echo("selected");}?> value="oldest">Oldest</option>
        <option <?php if($formula == 'dec'){echo("selected");}?> value="dec">Dec</option>
        <option <?php if($formula == 'current'){echo("selected");}?> value="current">Current</option>
        <option <?php if($formula == 'suggested'){echo("selected");}?> value="suggested">Suggested</option>
    </select>
    <br><br>
    <input type="checkbox" id="divide_rp" name="divide_rp" value="divide" <?php if($divide_rp){echo("checked");}?>  >
    <label for="divide_rp">Divide RPs by prepaid ( works only on dec, current or suggested )</label><br><br>
    <input type="submit" name="Calc swap" value="Submit">
</form>


<?php

function create_rps($date, $frequency, $num_rps_created = 1)
{
    if(1 > $num_rps_created)
    {
        echo  str_repeat("<br>", 1) . "No recurring payments will be created !" .str_repeat("<br>", 2);
        return;
    }

    $i = 0;
    echo  str_repeat("<br>", 1) . "{$num_rps_created} recurring payments will be created with these billing dates:" .str_repeat("<br>", 2);
    while ($i < $num_rps_created) {
        echo "RP:" . ($i+1) . str_repeat("<br>", 1);
        echo "Billing_date:" . add_date_by_frequency($date, $frequency, $i) . str_repeat("<br>", 2);
        $i++;
    }
}

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



    echo "<h2>Parameters:</h2>";
    echo 'Duration:'. $duration;
    echo str_repeat("<br>", 1);
    echo 'Prepaid:'. $prepaid;
    echo str_repeat("<br>", 2);
    echo 'Subscription_start:' . $start_date . str_repeat("<br>", 1);
    echo 'Subscription_end:' . $subscription_end . str_repeat("<br>", 2);

    $first_billing_date = add_date_by_frequency(
        $swap_subscription_start_date,
        $frequency,
        $prepaid
    );

    echo 'First_billing_date:' . $first_billing_date . str_repeat("<br>", 2);


    $time = Carbon::make($swap_date)->diff($start_date);
    $diff_months = ($time->y ? $time->y * 12 : $time->m);
    echo "Diffrence between subscription_start date and swap date {$diff_months}:months {$time->d}:days";
    echo "<br>";

    $time = Carbon::make($swap_date)->diff($subscription_end);
    $diff_months = ($time->y ? $time->y * 12 : $time->m);
    echo "Diffrence between subscription_end date and swap date {$diff_months}:months {$time->d}:days";
    echo "<br><br>";


    switch ($formula) {
        case 'oldest':
            print_oldest_results();
            break;
        case 'dec':
            print_dec_results();
            break;
        case 'current':
            print_current_results();
            break;
        case 'suggested':
            print_suggested_results();
            break;
        default:
    }

    //$diff_months = Carbon::make($swap_date)->diffInMonths($start_date);
    //$another_subscription_duration = (($duration ) - $paid_failed) - $diff_months;
    //echo 'Subscription_duration__formula_calc_from_start_date:' . $another_subscription_duration;

}

function print_oldest_results()
{
    global $first_billing_date, $frequency, $duration, $prepaid, $paid_failed;
    $subscription_duration  = ($duration - $prepaid) - $paid_failed;
    $number_of_rps = $subscription_duration;
    echo "<h2>Oldest formula:</h2>";
    echo  'Original subscription_duration:'. $subscription_duration. str_repeat("<br>", 1);
    echo  'Original Subscription_duration_prepaid:0' . str_repeat("<br>", 1);
    create_rps($first_billing_date, $frequency, $number_of_rps);
    echo "<br>";
}

function print_dec_results()
{
    global $divide_rp, $start_date, $subscription_end, $swap_date, $first_billing_date, $frequency, $duration, $prepaid, $paid_failed;
    echo "<h2>Dec formula:</h2>";

    $diff_subscription_duration = match ($frequency) {
        'daily' =>  Carbon::make($swap_date)->startOfDay()->diffInDays($subscription_end),
        'weekly' =>  Carbon::make($swap_date)->startOfDay()->diffInWeeks($subscription_end),
        'monthly' =>  Carbon::make($swap_date)->startOfDay()->diffInMonths($subscription_end) + 1,
        'yearly' =>  Carbon::make($swap_date)->startOfDay()->diffInYears($subscription_end),
    };

    //echo  $settled;
    $diff_months_start_date= Carbon::make($swap_date)->startOfDay()->diffInMonths($start_date);

    $subscription_duration = ($diff_subscription_duration);
    $subscription_duration_prepaid
        = max(($prepaid + $paid_failed)
        - $diff_months_start_date, 0);

    $number_of_rps = ($subscription_duration - $subscription_duration_prepaid);
    if($divide_rp)
    {
        if( $subscription_duration_prepaid > 1 ){
            $number_of_rps = round($number_of_rps / $subscription_duration_prepaid,0);
        }
    }

    echo 'Subscription_duration_formula_calc_from_end_date:' . $subscription_duration;
    echo "<br>";
    echo 'Subscription_duration_prepaid:' . $subscription_duration_prepaid;
    echo "<br>";
    create_rps($first_billing_date, $frequency, $number_of_rps);

}

function print_current_results()
{
    global $divide_rp, $start_date, $subscription_end, $swap_date, $first_billing_date, $frequency, $duration, $prepaid, $paid_failed;
    echo "<h2>Current formula:</h2>";

    $diff_subscription_duration = match ($frequency) {
        'daily' =>  Carbon::make($swap_date)->startOfDay()->diffInDays($subscription_end),
        'weekly' =>  Carbon::make($swap_date)->startOfDay()->diffInWeeks($subscription_end),
        'monthly' =>  Carbon::make($swap_date)->startOfDay()->diffInMonths($subscription_end) + 1,
        'yearly' =>  Carbon::make($swap_date)->startOfDay()->diffInYears($subscription_end),
    };

    //echo  $settled;
    $diff_months_start_date= Carbon::make($swap_date)->startOfDay()->diffInMonths($start_date);

    $subscription_duration = ($diff_subscription_duration);
    $subscription_duration_prepaid
        = max(($prepaid + $paid_failed)
        - $diff_months_start_date, 0);

    $number_of_rps = ($subscription_duration - $subscription_duration_prepaid);
    if($divide_rp)
    {
        if( $subscription_duration_prepaid > 1 ){
            $number_of_rps = round($number_of_rps / $subscription_duration_prepaid,0);
        }

        $number_of_rps = ($number_of_rps == 1) ? 0 : $number_of_rps;
    }

    echo 'Subscription_duration_formula_calc_from_end_date:' . $subscription_duration;
    echo "<br>";
    echo 'Subscription_duration_prepaid:' . $subscription_duration_prepaid;
    echo "<br>";
    create_rps($first_billing_date, $frequency, $number_of_rps);
}

function print_suggested_results()
{
    global $divide_rp, $start_date, $subscription_end, $swap_date, $first_billing_date, $frequency, $duration, $prepaid, $paid_failed;
    echo "<h2>Suggested formula:</h2>";
    $deduct_period = 1;
    if(15 > Carbon::make($swap_date)->startOfDay()->diffInDays($subscription_end) )
    {
        $deduct_period = 0;
    }

    $diff_subscription_duration = match ($frequency) {
        'daily' =>  Carbon::make($swap_date)->startOfDay()->diffInDays($subscription_end),
        'weekly' =>  Carbon::make($swap_date)->startOfDay()->diffInWeeks($subscription_end),
        'monthly' =>  Carbon::make($swap_date)->startOfDay()->diffInMonths($subscription_end) + $deduct_period,
        'yearly' =>  Carbon::make($swap_date)->startOfDay()->diffInYears($subscription_end),
    };

    //echo  $settled;
    $diff_months_start_date= Carbon::make($swap_date)->startOfDay()->diffInMonths($start_date);

    $subscription_duration = ($diff_subscription_duration);
    $subscription_duration_prepaid
        = max(($prepaid + $paid_failed)
        - $diff_months_start_date, 0);

    $number_of_rps = ($subscription_duration - $subscription_duration_prepaid);
    if($divide_rp)
    {
        if( $prepaid > 1 ){
            $number_of_rps = round($number_of_rps / $prepaid,0);
        }

        $number_of_rps = ($number_of_rps == 1) ? 0 : $number_of_rps;
    }
    echo 'Subscription_duration_formula_calc_from_end_date:' . $subscription_duration;
    echo "<br>";
    echo 'Subscription_duration_prepaid:' . $subscription_duration_prepaid;
    echo "<br>";
    create_rps($first_billing_date, $frequency, $number_of_rps);
}

?>


</body>
</html>