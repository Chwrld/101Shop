<?php
require_once 'appliance_handler.php';

if(isset($_GET['customer_id'])) {
    $appliance_handler = new ApplianceHandler();
    $result = $appliance_handler->getAppliancesByCustomerId($_GET['customer_id']);
    
    echo '<option value="">Select Appliance</option>';
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['ApplianceID'] . '">' . 
                $row['Brand'] . ' ' . $row['Product'] . ' (' . $row['Model_No'] . ')</option>';
        }
    }
} else {
    echo '<option value="">Select Appliance</option>';
}
?> 