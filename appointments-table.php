<h2>Appointments</h2>
<table>
  <tr><th>Patient</th><th>Doctor</th><th>Date</th></tr>
  <?php while ($row = $appointments->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['user_name'] ?? 'N/A') ?></td>
      <td><?= htmlspecialchars($row['doctor_name'] ?? 'N/A') ?></td>
      <td><?= htmlspecialchars($row['appointment_date']) ?></td>
    </tr>
  <?php endwhile; ?>
</table>
