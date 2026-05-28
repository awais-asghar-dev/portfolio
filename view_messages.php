<?php
$host = 'localhost';
$db   = 'awais_portfolio';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Auto-create table if it doesn't exist (in case this page is accessed first)
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Handle Delete Action
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ?');
        $stmt->execute([$delete_id]);
        header('Location: view_messages.php?msg=deleted');
        exit;
    }
    
    // Fetch all messages
    $stmt = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC');
    $messages = $stmt->fetchAll();
    
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inbox — Portfolio Messages</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <style>
    :root {
      --teal: #00C9B1;
      --teal-dark: #00A896;
      --navy: #050E1F;
      --navy-mid: #0A1A30;
      --navy-card: #0D2040;
      --accent: #FF6B35;
      --white: #F0F6FF;
      --muted: #7A92B4;
      --border: rgba(0,201,177,0.15);
    }
    
    body {
      background: var(--navy);
      color: var(--white);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      padding: 3rem 1.5rem;
    }

    /* Grid background */
    body::after {
      content: '';
      position: fixed; inset: 0; z-index: -1;
      background-image:
        linear-gradient(rgba(0,201,177,.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,201,177,.02) 1px, transparent 1px);
      background-size: 50px 50px;
      pointer-events: none;
    }

    .container {
      max-width: 1100px;
    }

    h1 {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      letter-spacing: -1px;
      color: var(--teal);
    }

    .dashboard-card {
      background: var(--navy-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 16px 40px rgba(0,0,0,.3);
    }

    .search-box {
      background: var(--navy-mid);
      border: 1px solid var(--border);
      color: var(--white);
      border-radius: 8px;
      padding: 0.75rem 1.2rem;
      outline: none;
      transition: border-color 0.2s;
    }
    
    .search-box:focus {
      border-color: var(--teal);
      box-shadow: 0 0 0 3px rgba(0,201,177,.1);
    }

    .table {
      color: var(--white) !important;
      margin-bottom: 0;
      vertical-align: middle;
    }

    .table thead th {
      background: var(--navy-mid) !important;
      color: var(--teal) !important;
      border-bottom: 1px solid var(--border) !important;
      font-weight: 600;
      padding: 1rem;
      text-transform: uppercase;
      font-size: 0.78rem;
      letter-spacing: 0.05em;
    }

    .table tbody tr {
      border-bottom: 1px solid rgba(255,255,255,.05) !important;
      transition: background 0.2s;
    }

    .table tbody tr:hover {
      background: rgba(0,201,177,0.03) !important;
    }

    .table tbody td {
      background: transparent !important;
      padding: 1.2rem 1rem;
      font-size: 0.9rem;
      color: var(--white) !important;
    }

    .btn-delete {
      color: #ff5252;
      background: transparent;
      border: 1px solid rgba(255,82,82,0.2);
      border-radius: 6px;
      padding: 0.4rem 0.8rem;
      font-size: 0.8rem;
      font-weight: 600;
      transition: all 0.2s;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      white-space: nowrap;
      text-decoration: none;
    }

    .btn-delete:hover {
      background: #ff5252;
      color: var(--navy) !important;
      box-shadow: 0 0 15px rgba(255,82,82,0.3);
    }

    .btn-back {
      background: transparent;
      border: 1.5px solid var(--teal);
      color: var(--teal);
      padding: 0.6rem 1.4rem;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.25s;
    }

    .btn-back:hover {
      background: var(--teal);
      color: var(--navy);
      box-shadow: 0 0 20px rgba(0,201,177,.3);
    }

    .badge-date {
      font-size: 0.78rem;
      color: var(--muted);
    }

    .no-messages {
      color: var(--muted);
      text-align: center;
      padding: 4rem 1rem;
      font-size: 1.1rem;
    }

    .toast-msg {
      background: rgba(0,201,177,0.1);
      border: 1px solid var(--teal);
      color: var(--teal);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-5">
    <div>
      <a href="index.html" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
    <div class="text-end">
      <h1>Contact Inbox</h1>
      <p style="color:var(--muted); font-size:0.9rem; margin:0;">Viewing all messages from your website</p>
    </div>
  </div>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="toast-msg">
      <i class="fas fa-check-circle"></i> Message successfully deleted.
    </div>
  <?php endif; ?>

  <div class="dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <h3 style="font-weight:700; font-family:'Syne', sans-serif; margin:0;" class="fs-5">Messages List (<?= count($messages) ?>)</h3>
      <input type="text" id="searchInput" class="search-box w-100 w-sm-auto" style="max-width:300px;" placeholder="Search inbox...">
    </div>

    <?php if (empty($messages)): ?>
      <div class="no-messages">
        <i class="far fa-envelope-open fa-3x mb-3" style="color:var(--teal); opacity:0.5;"></i>
        <p>No messages received yet.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table" id="messagesTable">
          <thead>
            <tr>
              <th style="width: 15%;">Sender</th>
              <th style="width: 20%;">Email</th>
              <th style="width: 20%;">Subject</th>
              <th style="width: 30%;">Message</th>
              <th style="width: 14%;">Received At</th>
              <th class="text-center" style="width: 1%; white-space: nowrap;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $row): ?>
              <tr>
                <td style="font-weight: 600; white-space: nowrap;"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td style="white-space: nowrap;"><a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color:var(--teal); text-decoration:none;"><?= htmlspecialchars($row['email']) ?></a></td>
                <td style="font-weight: 500; min-width: 150px;"><?= htmlspecialchars($row['subject']) ?></td>
                <td style="font-size:0.85rem; color:var(--muted) !important; min-width: 220px;"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td class="badge-date" style="white-space: nowrap;"><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                <td class="text-center" style="white-space: nowrap;">
                  <a href="view_messages.php?delete_id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this message?');">
                    <i class="fas fa-trash-alt"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Dynamic search filtering
  document.getElementById('searchInput').addEventListener('keyup', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#messagesTable tbody tr');
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      if(text.includes(query)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
</script>

</body>
</html>
