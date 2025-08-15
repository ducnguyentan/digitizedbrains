<?php
/**
 * Database Connection Configuration for DigitizedBrains Website
 * Kết nối cơ sở dữ liệu cho website DigitizedBrains
 */

class DatabaseConnection {
    private $host = 'localhost';
    private $dbname = 'digitizedbrains_data';
    private $username = 'root'; // Change as needed
    private $password = '';     // Change as needed
    private $pdo;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function getPDO() {
        return $this->pdo;
    }
    
    /**
     * Get or create user session
     */
    public function getOrCreateUser($sessionId, $ipAddress = null, $userAgent = null) {
        try {
            // Check if user exists
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE session_id = ?");
            $stmt->execute([$sessionId]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update last visit and increment visit count
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET last_visit = CURRENT_TIMESTAMP, total_visits = total_visits + 1 
                    WHERE session_id = ?
                ");
                $stmt->execute([$sessionId]);
                return $user;
            } else {
                // Create new user
                $stmt = $this->pdo->prepare("
                    INSERT INTO users (session_id, ip_address, user_agent) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$sessionId, $ipAddress, $userAgent]);
                
                return [
                    'session_id' => $sessionId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'first_visit' => date('Y-m-d H:i:s'),
                    'total_visits' => 1
                ];
            }
        } catch (PDOException $e) {
            error_log("Error in getOrCreateUser: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log page visit
     */
    public function logPageVisit($sessionId, $pageUrl, $pageTitle = null, $visitDuration = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO page_visits (session_id, page_url, page_title, visit_duration) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$sessionId, $pageUrl, $pageTitle, $visitDuration]);
        } catch (PDOException $e) {
            error_log("Error logging page visit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save contact form submission
     */
    public function saveContactSubmission($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO contact_submissions 
                (session_id, name, email, company, phone, message, page_source) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['session_id'],
                $data['name'],
                $data['email'],
                $data['company'] ?? null,
                $data['phone'] ?? null,
                $data['message'],
                $data['page_source'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error saving contact submission: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save service request
     */
    public function saveServiceRequest($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO service_requests 
                (session_id, service_type, company_name, contact_person, email, phone, 
                 company_size, industry, specific_needs, budget_range, timeline, page_source) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['session_id'],
                $data['service_type'],
                $data['company_name'] ?? null,
                $data['contact_person'],
                $data['email'],
                $data['phone'] ?? null,
                $data['company_size'] ?? null,
                $data['industry'] ?? null,
                $data['specific_needs'] ?? null,
                $data['budget_range'] ?? null,
                $data['timeline'] ?? null,
                $data['page_source'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error saving service request: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log chatbot conversation
     */
    public function logChatbotConversation($sessionId, $userMessage, $botResponse = null, $pageContext = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO chatbot_conversations 
                (session_id, user_message, bot_response, page_context) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$sessionId, $userMessage, $botResponse, $pageContext]);
        } catch (PDOException $e) {
            error_log("Error logging chatbot conversation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log AI agent interaction
     */
    public function logAIAgentInteraction($sessionId, $agentType, $interactionType, $interactionData = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ai_agent_interactions 
                (session_id, agent_type, interaction_type, interaction_data) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $sessionId, 
                $agentType, 
                $interactionType, 
                $interactionData ? json_encode($interactionData) : null
            ]);
        } catch (PDOException $e) {
            error_log("Error logging AI agent interaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log file download
     */
    public function logFileDownload($sessionId, $fileName, $fileType, $fileCategory, $downloadUrl) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO file_downloads 
                (session_id, file_name, file_type, file_category, download_url) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$sessionId, $fileName, $fileType, $fileCategory, $downloadUrl]);
        } catch (PDOException $e) {
            error_log("Error logging file download: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user language preference
     */
    public function updateLanguagePreference($sessionId, $previousLang, $newLang, $pageUrl) {
        try {
            // Update user's preferred language
            $stmt = $this->pdo->prepare("UPDATE users SET preferred_language = ? WHERE session_id = ?");
            $stmt->execute([$newLang, $sessionId]);
            
            // Log the language change
            $stmt = $this->pdo->prepare("
                INSERT INTO language_changes 
                (session_id, previous_language, new_language, page_url) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$sessionId, $previousLang, $newLang, $pageUrl]);
        } catch (PDOException $e) {
            error_log("Error updating language preference: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user analytics
     */
    public function getUserAnalytics($limit = 100) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user_summary ORDER BY last_visit DESC LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting user analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular pages
     */
    public function getPopularPages($limit = 20) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM popular_pages LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting popular pages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get service requests analytics
     */
    public function getServiceAnalytics() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM service_analytics");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting service analytics: " . $e->getMessage());
            return [];
        }
    }
}

// Utility function to get session ID
function getSessionId() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['digitizedbrains_session_id'])) {
        $_SESSION['digitizedbrains_session_id'] = bin2hex(random_bytes(16));
    }
    
    return $_SESSION['digitizedbrains_session_id'];
}

// Utility function to get client IP
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            return trim($ips[0]);
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
?>