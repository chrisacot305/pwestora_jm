<?php
/**
 * modules/overview_lessor.php
 * Lessor Dashboard Overview with Rented Spaces showcase and property photos.
 */
$lessorId = $user['id'];

$activeLeases = $pdo->prepare('SELECT COUNT(*) FROM tenants WHERE lessor_id = ?');
$activeLeases->execute([$lessorId]);
$activeLeases = (int) $activeLeases->fetchColumn();

$pendingApps = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE lessor_id = ? AND status = "pending"');
$pendingApps->execute([$lessorId]);
$pendingApps = (int) $pendingApps->fetchColumn();

$openTickets = $pdo->prepare('SELECT COUNT(*) FROM maintenance_tickets WHERE lessor_id = ? AND stage < 7');
$openTickets->execute([$lessorId]);
$openTickets = (int) $openTickets->fetchColumn();

$openViolations = $pdo->prepare('SELECT COUNT(*) FROM violations WHERE lessor_id = ?');
$openViolations->execute([$lessorId]);
$openViolations = (int) $openViolations->fetchColumn();

// Rented Spaces & Active Tenants with Property Photos
$rentedSpaces = $pdo->prepare(
    'SELECT t.*, p.name AS property_name, p.type AS property_type, p.address AS property_address,
            (SELECT photo_path FROM property_photos pp WHERE pp.property_id = p.id ORDER BY pp.id ASC LIMIT 1) AS cover_photo
     FROM tenants t
     JOIN properties p ON p.id = t.property_id
     WHERE t.lessor_id = ?
     ORDER BY t.created_at DESC LIMIT 6'
);
$rentedSpaces->execute([$lessorId]);
$rentedSpaces = $rentedSpaces->fetchAll();

// Pending Installment Requests with Property Info
$pendingInstallments = $pdo->prepare(
    'SELECT ir.*, t.tenant_name, p.name AS property_name,
            (SELECT photo_path FROM property_photos pp WHERE pp.property_id = p.id ORDER BY pp.id ASC LIMIT 1) AS cover_photo
     FROM installment_requests ir
     JOIN tenants t ON t.id = ir.tenant_id
     JOIN properties p ON p.id = t.property_id
     WHERE ir.lessor_id = ? AND ir.status = "pending"
     ORDER BY ir.created_at DESC'
);
$pendingInstallments->execute([$lessorId]);
$pendingInstallments = $pendingInstallments->fetchAll();

$ticketStages = ['Submitted','Under Review','Approved','Contractor Assigned','Scheduled','In Progress','Inspection','Completed'];
$openTicketRows = $pdo->prepare(
    'SELECT mt.*, t.tenant_name, p.name AS property_name,
            (SELECT photo_path FROM property_photos pp WHERE pp.property_id = p.id ORDER BY pp.id ASC LIMIT 1) AS cover_photo
     FROM maintenance_tickets mt
     JOIN tenants t ON t.id = mt.tenant_id
     JOIN properties p ON p.id = t.property_id
     WHERE mt.lessor_id = ? AND mt.stage < 7
     ORDER BY mt.created_at DESC'
);
$openTicketRows->execute([$lessorId]);
$openTicketRows = $openTicketRows->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
  <div>
    <h2 style="margin:0 0 4px; font-size:22px; font-weight:800; color:var(--ink-900);">Dashboard Overview</h2>
    <p style="color:var(--ink-500); margin:0; font-size:13.5px;">Portfolio snapshot across your rented commercial spaces and operations.</p>
  </div>
  <a href="/dashboard.php?page=listings&add=1" class="btn btn-primary" style="padding:9px 18px; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
    <i class="bi bi-plus-lg"></i> Add Property
  </a>
</div>

<!-- KPI Summary Grid -->
<div class="kpi-grid" style="margin-bottom:24px;">
  <div class="card" style="border-left:4px solid var(--primary); padding:16px 20px;">
    <div class="kpi-label" style="font-size:12px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">Active Leases</div>
    <div class="kpi-value" style="font-size:28px; font-weight:800; color:var(--primary); margin-top:4px;"><?= $activeLeases ?></div>
  </div>
  <div class="card" style="border-left:4px solid var(--warning); padding:16px 20px;">
    <div class="kpi-label" style="font-size:12px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">Pending Applications</div>
    <div class="kpi-value" style="font-size:28px; font-weight:800; color:var(--warning); margin-top:4px;"><?= $pendingApps ?></div>
  </div>
  <div class="card" style="border-left:4px solid #3B82F6; padding:16px 20px;">
    <div class="kpi-label" style="font-size:12px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">Open Maintenance</div>
    <div class="kpi-value" style="font-size:28px; font-weight:800; color:#3B82F6; margin-top:4px;"><?= $openTickets ?></div>
  </div>
  <div class="card" style="border-left:4px solid var(--error); padding:16px 20px;">
    <div class="kpi-label" style="font-size:12px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">Open Violations</div>
    <div class="kpi-value" style="font-size:28px; font-weight:800; color:var(--error); margin-top:4px;"><?= $openViolations ?></div>
  </div>
</div>

<!-- Active Rented Spaces Showcase -->
<div style="margin-bottom:28px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <div>
      <h3 style="margin:0; font-size:16px; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:8px;">
        <i class="bi bi-buildings" style="color:var(--primary);"></i> Rented Commercial Spaces
      </h3>
      <p style="margin:2px 0 0; font-size:12.5px; color:var(--ink-500);">Currently leased units with active tenants.</p>
    </div>
    <a href="/dashboard.php?page=tenants" style="font-size:12.5px; font-weight:600; color:var(--primary); text-decoration:none;">
      View all tenants <i class="bi bi-arrow-right"></i>
    </a>
  </div>

  <?php if (!$rentedSpaces): ?>
    <div class="card" style="padding:28px; text-align:center; background:#fff; border:1px dashed var(--border);">
      <i class="bi bi-shop" style="font-size:32px; color:var(--ink-300);"></i>
      <p style="color:var(--ink-500); font-size:13.5px; margin:8px 0 0;">No active rented spaces yet. When an application is approved, leased properties will appear here.</p>
    </div>
  <?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
      <?php foreach ($rentedSpaces as $rs): ?>
        <div class="card" style="padding:0; overflow:hidden; border-radius:12px; border:1px solid var(--border); background:#fff; transition:transform 0.18s ease, box-shadow 0.18s ease; cursor:pointer;"
             onclick="window.location.href='/dashboard.php?page=tenants'"
             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';"
             onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
          
          <!-- Image Section -->
          <div style="height:130px; width:100%; position:relative; background:#1e293b; overflow:hidden;">
            <?php if ($rs['cover_photo']): ?>
              <img src="/<?= htmlspecialchars($rs['cover_photo']) ?>" alt="<?= htmlspecialchars($rs['property_name']) ?>"
                   style="width:100%; height:100%; object-fit:cover; opacity:0.92;">
            <?php else: ?>
              <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, #102340 0%, #264875 100%); color:#fff; font-size:32px;">
                <i class="bi bi-building"></i>
              </div>
            <?php endif; ?>

            <!-- Space Type Badge -->
            <span style="position:absolute; top:10px; left:10px; background:rgba(16,35,64,0.85); color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; backdrop-filter:blur(4px); text-transform:uppercase; letter-spacing:0.4px;">
              <?= htmlspecialchars($rs['property_type'] ?: 'Commercial') ?>
            </span>

            <!-- Active Status Badge -->
            <span style="position:absolute; top:10px; right:10px; background:#10b981; color:#fff; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.2);">
              <i class="bi bi-check-circle-fill"></i> Leased
            </span>
          </div>

          <!-- Content Section with High Contrast -->
          <div style="padding:14px 16px;">
            <h4 style="margin:0 0 6px; font-size:14.5px; font-weight:800; color:var(--ink-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?= htmlspecialchars($rs['property_name']) ?>
            </h4>

            <div style="display:flex; align-items:center; gap:6px; margin-bottom:8px; font-size:12.5px; color:var(--ink-700); font-weight:600;">
              <i class="bi bi-person-circle" style="color:var(--primary); font-size:13px;"></i>
              <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($rs['tenant_name']) ?></span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:10px; border-top:1px solid #f1f5f9; margin-top:6px;">
              <div>
                <span style="font-size:11px; color:var(--ink-500); display:block; line-height:1;">Monthly Rent</span>
                <strong style="font-size:14px; color:var(--primary); font-weight:800;">₱<?= number_format($rs['monthly_rent'], 0) ?></strong>
              </div>
              <span style="font-size:11.5px; color:var(--primary); font-weight:600; display:inline-flex; align-items:center; gap:3px;">
                Details <i class="bi bi-chevron-right" style="font-size:10px;"></i>
              </span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Recent Operational Activity (Installments & Maintenance) -->
<div class="two-col">
  <!-- Installment Requests -->
  <div class="card" style="padding:18px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
      <p style="font-weight:700; font-size:13.5px; color:var(--ink-900); margin:0; display:flex; align-items:center; gap:6px;">
        <i class="bi bi-credit-card-2-front" style="color:var(--primary);"></i> Pending Installment Requests
      </p>
      <a href="/dashboard.php?page=installments" style="font-size:11.5px; color:var(--primary); font-weight:600; text-decoration:none;">View all</a>
    </div>

    <?php if (!$pendingInstallments): ?>
      <p style="color:var(--ink-500); font-size:13px; margin:10px 0;">Nothing pending right now.</p>
    <?php else: foreach ($pendingInstallments as $r): ?>
      <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border); gap:12px;">
        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
          <?php if ($r['cover_photo']): ?>
            <img src="/<?= htmlspecialchars($r['cover_photo']) ?>" alt="Property" style="width:38px; height:38px; border-radius:6px; object-fit:cover; flex-shrink:0; border:1px solid var(--border);">
          <?php else: ?>
            <div style="width:38px; height:38px; border-radius:6px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:var(--ink-500); flex-shrink:0;">
              <i class="bi bi-building" style="font-size:15px;"></i>
            </div>
          <?php endif; ?>
          <div style="min-width:0;">
            <p style="margin:0; font-size:13px; font-weight:700; color:var(--ink-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?= htmlspecialchars($r['tenant_name']) ?>
            </p>
            <p style="margin:2px 0 0; font-size:11.5px; color:var(--ink-500); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?= htmlspecialchars($r['property_name']) ?> · <?= htmlspecialchars($r['reason'] ?: 'Custom payment plan') ?>
            </p>
          </div>
        </div>
        <span class="badge" style="background:var(--accent-soft); color:var(--primary); font-size:11px; font-weight:700; padding:4px 8px; white-space:nowrap;">
          <?= htmlspecialchars($r['plan_label']) ?>
        </span>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Maintenance SLA Watch -->
  <div class="card" style="padding:18px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
      <p style="font-weight:700; font-size:13.5px; color:var(--ink-900); margin:0; display:flex; align-items:center; gap:6px;">
        <i class="bi bi-tools" style="color:var(--primary);"></i> Maintenance SLA Watch
      </p>
      <a href="/dashboard.php?page=maintenance" style="font-size:11.5px; color:var(--primary); font-weight:600; text-decoration:none;">View all</a>
    </div>

    <?php if (!$openTicketRows): ?>
      <p style="color:var(--ink-500); font-size:13px; margin:10px 0;">No open tickets right now.</p>
    <?php else: foreach ($openTicketRows as $t): ?>
      <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border); gap:12px;">
        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
          <?php if ($t['cover_photo']): ?>
            <img src="/<?= htmlspecialchars($t['cover_photo']) ?>" alt="Property" style="width:38px; height:38px; border-radius:6px; object-fit:cover; flex-shrink:0; border:1px solid var(--border);">
          <?php else: ?>
            <div style="width:38px; height:38px; border-radius:6px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:var(--ink-500); flex-shrink:0;">
              <i class="bi bi-building" style="font-size:15px;"></i>
            </div>
          <?php endif; ?>
          <div style="min-width:0;">
            <p style="margin:0; font-size:13px; font-weight:700; color:var(--ink-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?= htmlspecialchars($t['title']) ?>
            </p>
            <p style="margin:2px 0 0; font-size:11.5px; color:var(--ink-500); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?= htmlspecialchars($t['tenant_name']) ?> · <?= htmlspecialchars($t['property_name']) ?>
            </p>
          </div>
        </div>
        <span class="badge" style="background:var(--warning-soft); color:var(--warning); font-size:11px; font-weight:700; padding:4px 8px; white-space:nowrap;">
          <?= $ticketStages[$t['stage']] ?? 'In Progress' ?>
        </span>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>