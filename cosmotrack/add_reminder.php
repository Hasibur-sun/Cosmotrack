<?php
include "includes/db.php";

$event_id = $_GET['event_id'] ?? 0;

/*
Temporary demo user.
Later can be replaced with session user ID.
*/
$user_id = 1;

/* Get selected event */
$event_sql = "SELECT * FROM public_events WHERE event_id = $event_id";

$event_result = mysqli_query($conn, $event_sql);

$event = mysqli_fetch_assoc($event_result);

if (!$event) {
    echo "Event not found.";
    exit();
}

/*
Reminder will trigger 1 day before event
*/
$reminder_date = date(
    'Y-m-d',
    strtotime($event['event_date'] . ' -1 day')
);

$reminder_time = '20:00:00';

/* Insert reminder */
$sql = "INSERT INTO reminders
(user_id, event_id, reminder_date, reminder_time, reminder_method, status)

VALUES

('$user_id',
 '$event_id',
 '$reminder_date',
 '$reminder_time',
 'Dashboard',
 'Pending')";

if (mysqli_query($conn, $sql)) {

    header("Location: reminders.php");
    exit();

} else {

    echo "Reminder insert failed: " . mysqli_error($conn);

}
?>