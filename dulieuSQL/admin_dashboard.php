<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DigitizedBrains Analytics</title>
    <link href="../css/tailwind.min.css" rel="stylesheet">
    <style>
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .chart-container { height: 300px; }
    </style>
</head>
<body class="bg-gray-100">
    <?php
    require_once 'db_connection.php';
    
    try {
        $db = new DatabaseConnection();
        
        // Get statistics
        $totalUsers = $db->getPDO()->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalContacts = $db->getPDO()->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
        $totalServiceRequests = $db->getPDO()->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();
        $totalPageViews = $db->getPDO()->query("SELECT COUNT(*) FROM page_visits")->fetchColumn();
        
        // Get recent data
        $recentContacts = $db->getPDO()->query("
            SELECT name, email, company, message, submitted_at 
            FROM contact_submissions 
            ORDER BY submitted_at DESC LIMIT 10
        ")->fetchAll();
        
        $recentServiceRequests = $db->getPDO()->query("
            SELECT service_type, company_name, contact_person, email, request_status, created_at 
            FROM service_requests 
            ORDER BY created_at DESC LIMIT 10
        ")->fetchAll();
        
        $popularPages = $db->getPopularPages(10);
        $userAnalytics = $db->getUserAnalytics(20);
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
    ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">DigitizedBrains Analytics Dashboard</h1>
            <p class="text-gray-600">Thống kê và phân tích dữ liệu người dùng website</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card stat-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Tổng người dùng</p>
                        <p class="text-2xl font-bold"><?= number_format($totalUsers) ?></p>
                    </div>
                    <div class="text-3xl">👥</div>
                </div>
            </div>
            
            <div class="card stat-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Liên hệ</p>
                        <p class="text-2xl font-bold"><?= number_format($totalContacts) ?></p>
                    </div>
                    <div class="text-3xl">📝</div>
                </div>
            </div>
            
            <div class="card stat-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Yêu cầu dịch vụ</p>
                        <p class="text-2xl font-bold"><?= number_format($totalServiceRequests) ?></p>
                    </div>
                    <div class="text-3xl">🔧</div>
                </div>
            </div>
            
            <div class="card stat-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Lượt xem trang</p>
                        <p class="text-2xl font-bold"><?= number_format($totalPageViews) ?></p>
                    </div>
                    <div class="text-3xl">📊</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Contacts -->
            <div class="card p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Liên hệ gần đây</h2>
                <div class="space-y-4">
                    <?php foreach ($recentContacts as $contact): ?>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($contact['name']) ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($contact['email']) ?></p>
                                <?php if ($contact['company']): ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($contact['company']) ?></p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-700 mt-1"><?= htmlspecialchars(substr($contact['message'], 0, 100)) ?>...</p>
                            </div>
                            <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($contact['submitted_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Service Requests -->
            <div class="card p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Yêu cầu dịch vụ gần đây</h2>
                <div class="space-y-4">
                    <?php foreach ($recentServiceRequests as $request): ?>
                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($request['contact_person']) ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($request['email']) ?></p>
                                <?php if ($request['company_name']): ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($request['company_name']) ?></p>
                                <?php endif; ?>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded"><?= htmlspecialchars($request['service_type']) ?></span>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded"><?= htmlspecialchars($request['request_status']) ?></span>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Popular Pages -->
            <div class="card p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Trang phổ biến nhất</h2>
                <div class="space-y-3">
                    <?php foreach ($popularPages as $page): ?>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($page['page_title'] ?: 'Unknown Page') ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars(parse_url($page['page_url'], PHP_URL_PATH)) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-blue-600"><?= number_format($page['visit_count']) ?></p>
                            <p class="text-xs text-gray-500">lượt xem</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- User Analytics -->
            <div class="card p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Phân tích người dùng</h2>
                <div class="space-y-3">
                    <?php foreach (array_slice($userAnalytics, 0, 10) as $user): ?>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800">Session: <?= htmlspecialchars(substr($user['session_id'], 0, 12)) ?>...</p>
                            <p class="text-sm text-gray-500">Ngôn ngữ: <?= htmlspecialchars($user['preferred_language']) ?></p>
                        </div>
                        <div class="text-right text-sm">
                            <p class="text-gray-600"><?= $user['total_visits'] ?> lượt / <?= $user['total_page_views'] ?> trang</p>
                            <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($user['last_visit'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="card p-6 mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Xuất dữ liệu</h2>
            <div class="flex gap-4">
                <a href="export_data.php?type=contacts" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                    Xuất danh sách liên hệ (CSV)
                </a>
                <a href="export_data.php?type=service_requests" 
                   class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
                    Xuất yêu cầu dịch vụ (CSV)
                </a>
                <a href="export_data.php?type=analytics" 
                   class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 transition-colors">
                    Xuất phân tích người dùng (CSV)
                </a>
            </div>
        </div>
    </div>

    <!-- Real-time updates -->
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
        
        // Show last updated time
        const now = new Date();
        const timeString = now.toLocaleString('vi-VN');
        document.title = `Admin Dashboard - Cập nhật: ${timeString}`;
    </script>
</body>
</html>