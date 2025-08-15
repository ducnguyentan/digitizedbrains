/**
 * Data Tracker for DigitizedBrains Website
 * Theo dõi và gửi dữ liệu người dùng về server
 */

class DigitizedBrainsTracker {
    constructor() {
        this.sessionId = this.getOrCreateSessionId();
        this.startTime = Date.now();
        this.formHandlerUrl = 'dulieuSQL/form_handler.php';
        this.pageUrl = window.location.href;
        this.pageTitle = document.title;
        
        this.init();
    }
    
    init() {
        // Track page visit
        this.trackPageVisit();
        
        // Track page unload (calculate visit duration)
        this.trackPageUnload();
        
        // Track form submissions
        this.trackForms();
        
        // Track chatbot interactions
        this.trackChatbot();
        
        // Track AI agent interactions
        this.trackAIAgentInteractions();
        
        // Track file downloads
        this.trackFileDownloads();
        
        // Track language changes
        this.trackLanguageChanges();
    }
    
    getOrCreateSessionId() {
        let sessionId = localStorage.getItem('digitizedbrains_session_id');
        if (!sessionId) {
            sessionId = 'db_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('digitizedbrains_session_id', sessionId);
        }
        return sessionId;
    }
    
    /**
     * Send data to server
     */
    async sendData(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
        
        try {
            const response = await fetch(this.formHandlerUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('Tracker:', action, result);
            }
            
            return result;
        } catch (error) {
            console.error('Tracking error:', error);
            return { success: false, error: error.message };
        }
    }
    
    /**
     * Track page visit
     */
    trackPageVisit() {
        this.sendData('page_visit', {
            page_url: this.pageUrl,
            page_title: this.pageTitle
        });
    }
    
    /**
     * Track page unload with visit duration
     */
    trackPageUnload() {
        window.addEventListener('beforeunload', () => {
            const visitDuration = Math.round((Date.now() - this.startTime) / 1000);
            
            // Use sendBeacon for reliable data sending on page unload
            const formData = new FormData();
            formData.append('action', 'page_visit');
            formData.append('page_url', this.pageUrl);
            formData.append('page_title', this.pageTitle);
            formData.append('visit_duration', visitDuration);
            
            navigator.sendBeacon(this.formHandlerUrl, formData);
        });
    }
    
    /**
     * Track form submissions
     */
    trackForms() {
        // Contact forms
        document.addEventListener('submit', (e) => {
            const form = e.target;
            
            if (form.classList.contains('contact-form') || form.id === 'contact-form') {
                e.preventDefault();
                this.handleContactForm(form);
            }
            
            if (form.classList.contains('newsletter-form') || form.id === 'newsletter-form') {
                e.preventDefault();
                this.handleNewsletterForm(form);
            }
            
            if (form.classList.contains('service-request-form') || form.id === 'service-request-form') {
                e.preventDefault();
                this.handleServiceRequestForm(form);
            }
        });
    }
    
    /**
     * Handle contact form submission
     */
    async handleContactForm(form) {
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            company: formData.get('company'),
            phone: formData.get('phone'),
            message: formData.get('message'),
            page_source: this.pageUrl
        };
        
        const result = await this.sendData('contact_form', data);
        
        if (result.success) {
            this.showSuccessMessage('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.');
            form.reset();
        } else {
            this.showErrorMessage(result.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }
    
    /**
     * Handle newsletter form submission
     */
    async handleNewsletterForm(form) {
        const formData = new FormData(form);
        const data = {
            email: formData.get('email'),
            name: formData.get('name')
        };
        
        const result = await this.sendData('newsletter_signup', data);
        
        if (result.success) {
            this.showSuccessMessage('Đăng ký newsletter thành công!');
            form.reset();
        } else {
            this.showErrorMessage(result.message || 'Đăng ký thất bại. Vui lòng thử lại.');
        }
    }
    
    /**
     * Handle service request form submission
     */
    async handleServiceRequestForm(form) {
        const formData = new FormData(form);
        const data = {
            service_type: formData.get('service_type'),
            company_name: formData.get('company_name'),
            contact_person: formData.get('contact_person'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            company_size: formData.get('company_size'),
            industry: formData.get('industry'),
            specific_needs: formData.get('specific_needs'),
            budget_range: formData.get('budget_range'),
            timeline: formData.get('timeline'),
            page_source: this.pageUrl
        };
        
        const result = await this.sendData('service_request', data);
        
        if (result.success) {
            this.showSuccessMessage('Yêu cầu dịch vụ đã được gửi thành công! Chúng tôi sẽ liên hệ với bạn sớm.');
            form.reset();
        } else {
            this.showErrorMessage(result.message || 'Gửi yêu cầu thất bại. Vui lòng thử lại.');
        }
    }
    
    /**
     * Track chatbot interactions
     */
    trackChatbot() {
        // Listen for chatbot events
        document.addEventListener('chatbot-message', (e) => {
            const { userMessage, pageContext } = e.detail;
            
            this.sendData('chatbot_message', {
                message: userMessage,
                page_context: pageContext || this.pageUrl
            });
        });
        
        // If chatbot exists, integrate with it
        if (window.chatbot) {
            const originalSend = window.chatbot.send;
            window.chatbot.send = (message) => {
                this.sendData('chatbot_message', {
                    message: message,
                    page_context: this.pageUrl
                });
                return originalSend.call(window.chatbot, message);
            };
        }
    }
    
    /**
     * Track AI agent interactions
     */
    trackAIAgentInteractions() {
        // Track AI Friends Talk interactions
        document.addEventListener('click', (e) => {
            if (e.target.id === 'launch-ai-friends-talk' || e.target.id === 'launch-ai-game') {
                this.sendData('ai_agent_interaction', {
                    agent_type: 'ai_friends_talk',
                    interaction_type: 'launch',
                    interaction_data: JSON.stringify({
                        button_id: e.target.id,
                        page_url: this.pageUrl
                    })
                });
            }
        });
        
        // Track iframe interactions (if possible)
        const iframe = document.querySelector('iframe[src*="ai-game"]');
        if (iframe) {
            iframe.addEventListener('load', () => {
                this.sendData('ai_agent_interaction', {
                    agent_type: 'ai_friends_talk',
                    interaction_type: 'iframe_loaded',
                    interaction_data: JSON.stringify({
                        iframe_src: iframe.src,
                        page_url: this.pageUrl
                    })
                });
            });
        }
    }
    
    /**
     * Track file downloads
     */
    trackFileDownloads() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;
            
            const href = link.href;
            const isPdf = href.includes('.pdf');
            const isDoc = href.includes('.doc') || href.includes('.docx');
            const isDownload = link.hasAttribute('download') || isPdf || isDoc;
            
            if (isDownload) {
                const fileName = link.getAttribute('download') || href.split('/').pop();
                const fileType = fileName.split('.').pop().toLowerCase();
                
                let fileCategory = 'document';
                if (href.includes('guide')) fileCategory = 'guide';
                else if (href.includes('whitepaper')) fileCategory = 'whitepaper';
                else if (href.includes('case-study')) fileCategory = 'case_study';
                else if (href.includes('template')) fileCategory = 'template';
                
                this.sendData('file_download', {
                    file_name: fileName,
                    file_type: fileType,
                    file_category: fileCategory,
                    download_url: href
                });
            }
        });
    }
    
    /**
     * Track language changes
     */
    trackLanguageChanges() {
        let currentLanguage = localStorage.getItem('preferredLanguage') || 'vi';
        
        // Watch for language changes
        const observer = new MutationObserver(() => {
            const newLanguage = localStorage.getItem('preferredLanguage') || 'vi';
            if (newLanguage !== currentLanguage) {
                this.sendData('language_change', {
                    previous_language: currentLanguage,
                    new_language: newLanguage,
                    page_url: this.pageUrl
                });
                currentLanguage = newLanguage;
            }
        });
        
        observer.observe(document.body, { attributes: true, subtree: true });
        
        // Also listen for storage events
        window.addEventListener('storage', (e) => {
            if (e.key === 'preferredLanguage' && e.newValue !== currentLanguage) {
                this.sendData('language_change', {
                    previous_language: currentLanguage,
                    new_language: e.newValue,
                    page_url: this.pageUrl
                });
                currentLanguage = e.newValue;
            }
        });
    }
    
    /**
     * Show success message to user
     */
    showSuccessMessage(message) {
        this.showMessage(message, 'success');
    }
    
    /**
     * Show error message to user
     */
    showErrorMessage(message) {
        this.showMessage(message, 'error');
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Animate in
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            toast.style.transition = 'transform 0.3s ease';
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 5 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 5000);
    }
}

// Initialize tracker when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.digitizedBrainsTracker = new DigitizedBrainsTracker();
});