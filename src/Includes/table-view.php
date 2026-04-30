<?php
require_once __DIR__ . '/../../Config/Database.php';

$conn = (new Database())->connect();
$conn->set_charset("utf8mb4");

// Fetch active parameters with their methods
$sql = "
    SELECT 
        tp.parameter_name,
        MAX(pbuc.is_slab_accredited) AS is_accredited
    FROM test_parameters AS tp
    LEFT JOIN parameter_base_unit_config AS pbuc 
        ON tp.parameter_id = pbuc.parameter_id
    WHERE tp.is_active = 1
    GROUP BY tp.parameter_id
";

$result = $conn->query($sql);
?>

<div class="table-responsive p-2">
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Parameter Name</th>
                <th class="text-center">SLAB Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="align-middle fw-medium"><?= htmlspecialchars($row['parameter_name']) ?></td>
                        <td class="align-middle text-center">
                            <?php if ($row['is_accredited'] == 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Accredited</span>
                            <?php else: ?>
                                <span class="badge bg-light text-secondary border border-secondary px-2 py-1">Not Accredited</span>
                            <?php endif; ?>
                        </td>
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