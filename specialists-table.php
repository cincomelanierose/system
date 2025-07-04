<h2>All Specialists</h2>
<table>
  <tr><th>Name</th><th>Specialization</th><th>Actions</th></tr>
  <?php while ($row = $doctors->fetch_assoc()): ?>
    <tr>
      <form method="POST">
        <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required></td>
        <td><input type="text" name="specialization" value="<?= htmlspecialchars($row['specialization']) ?>" required></td>
        <td>
          <input type="hidden" name="id" value="<?= $row['id'] ?>">
          <button type="submit" name="edit_specialist">Save</button>
      </form>
      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
        <button type="submit" name="delete_specialist" class="btn-danger">Delete</button>
      </form>
        </td>
    </tr>
  <?php endwhile; ?>
</table>
