<?php
require_once __DIR__ . '/../../Config/Database.php';

$conn = (new Database())->connect();
$conn->set_charset("utf8mb4");

// Fetch active parameters with their methods
$sql = "
    SELECT 
        CONCAT(tp.parameter_name, ' (', tp.base_unit, ')') AS parameter_name,
        GROUP_CONCAT(tm.method_name SEPARATOR ', ') AS method_names
    FROM test_parameters AS tp
    LEFT JOIN parameter_methods AS pm 
        ON tp.parameter_id = pm.parameter_id
    LEFT JOIN test_methods AS tm 
        ON pm.method_id = tm.method_id
    WHERE tp.is_active = 1
    GROUP BY tp.parameter_id
";

$result = $conn->query($sql);
?>

<div class="table-responsive p-2">
<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
            <th>Parameter Name (Unit)</th>
            <th>Methods</th>
        </tr>
    </thead>
    <tbody>
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['parameter_name']) ?></td>
                    <td><?= htmlspecialchars($row['method_names'] ?: '--') ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="2" class="text-center text-muted">No active parameters found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
