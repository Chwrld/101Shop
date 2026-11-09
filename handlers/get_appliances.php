<?php
require_once 'appliance_handler.php';

if(isset($_GET['customer_id'])) {
    $appliance_handler = new ApplianceHandler();
    $result = $appliance_handler->getAppliancesByCustomerId($_GET['customer_id']);
    
    if($result->num_rows > 0) {
        echo '<div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Product</th>
                            <th>Model No</th>
                            <th>Serial No</th>
                            <th>Warranty End</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        while($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>'.$row['Brand'].'</td>
                    <td>'.$row['Product'].'</td>
                    <td>'.$row['Model_No'].'</td>
                    <td>'.$row['Serial_No'].'</td>
                    <td>'.$row['Warranty_end'].'</td>
                    <td>'.$row['Category'].'</td>
                    <td>'.$row['Status'].'</td>
                    <td>
                        <a href="#" class="edit-appliance-btn" 
                            data-id="'.$row['ApplianceID'].'"
                            data-brand="'.$row['Brand'].'"
                            data-product="'.$row['Product'].'"
                            data-model="'.$row['Model_No'].'"
                            data-serial="'.$row['Serial_No'].'"
                            data-warranty="'.$row['Warranty_end'].'"
                            data-category="'.$row['Category'].'"
                            data-status="'.$row['Status'].'">
                            <i class="material-icons" data-toggle="tooltip" title="Edit">&#xE254;</i>
                        </a>
                        <a href="#" class="delete-appliance-btn" data-id="'.$row['ApplianceID'].'">
                            <i class="material-icons" data-toggle="tooltip" title="Delete">&#xE872;</i>
                        </a>
                    </td>
                </tr>';
        }
        
        echo '</tbody></table></div>';
    } else {
        echo '<p>No appliances found for this customer.</p>';
    }
}
?> 