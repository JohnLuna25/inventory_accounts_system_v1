<?php
// This is to diplay the inventory thru a table form.
$sql = "SELECT * FROM sorcom_readymix_inventory
                    WHERE 
                        (description IS NOT NULL AND description <> '') OR
                        (batch IS NOT NULL AND batch <> '') OR
                        (remarks IS NOT NULL AND remarks <> '') OR
                        (stocks_in IS NOT NULL AND stocks_in <> 0) OR
                        (stocks_out IS NOT NULL AND stocks_out <> 0) OR
                        (date_added IS NOT NULL AND date_added <> '') OR
                        (design_mix_id IS NOT NULL AND design_mix_id <> '')
                    ORDER BY date_added DESC, id ASC";

$result = $conn->query($sql);

echo '<table>
            <thead>
            <tr>
            <th>ID</th><th>Date</th><th>Design Mix ID</th><th>Description</th><th>Batch</th>
            <th>Stocks In</th><th>Stocks Out</th><th>Remarks</th><th>Actions</th>

            </tr></thead><tbody>';

while ($row = $result->fetch_assoc()) {
    $row_json = htmlspecialchars(json_encode($row), ENT_QUOTES);
    echo '<tr>';
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['date_added']}</td>";
    echo "<td>{$row['design_mix_id']}</td>";
    echo "<td>{$row['description']}</td>";
    echo "<td>{$row['batch']}</td>";
    echo "<td>{$row['stocks_in']}</td>";
    echo "<td>{$row['stocks_out']}</td>";
    echo "<td>{$row['remarks']}</td>";
    echo "<td style='display: flex; gap: 5px; justify-content: center;'>
                        <button onclick='openEditModal($row_json)' 
                                style='background:#4CAF50; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'>
                            Edit
                        </button>
                        <form method='POST' style='margin:0;' onsubmit='return confirm(\"Are you sure you want to delete this?\")'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='hidden' name='action' value='delete'>
                            <button type='submit' 
                                    style='background:#f44336; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'>
                                Delete
                            </button>
                        </form>
                    </td>";

    echo '</tr>';
}
echo '</tbody></table>';
$conn->close();
?>