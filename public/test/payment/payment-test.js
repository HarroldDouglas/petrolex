// Payment Test Configuration
const PAYMENT_CONFIG = {
    endpoints: {
        mtn: '/api/payment/test/mtn',
        orange: '/api/payment/test/orange'
    },
    
    // Test phone numbers for different scenarios
    testNumbers: {
        mtn: {
            success: '677000001',
            pending: '677000002', 
            failure: '677000003'
        },
        orange: {
            success: '699000001',
            pending: '699000002',
            failure: '699000003'
        }
    }
};

// Statistics counters
let stats = {
    success: 0,
    pending: 0,
    error: 0
};

// Transaction tracking
let recentTransactions = [];

// Console logging system
class PaymentConsole {
    constructor() {
        this.output = document.getElementById('consoleOutput');
        this.lastCallbackCheck = Date.now();
        this.startCallbackPolling();
    }

    log(message, type = 'info') {
        const timestamp = new Date().toLocaleString('fr-FR');
        
        // Auto-detect PENDING status and override type
        if (message.includes('PENDING') && type !== 'error') {
            type = 'pending';
        }
        
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry log-${type}`;
        
        // Add special styling for success messages
        if (type === 'success') {
            logEntry.style.backgroundColor = 'rgba(0, 255, 0, 0.1)';
            logEntry.style.borderLeft = '4px solid #00ff00';
        }
        
        // Add special styling for pending messages
        if (type === 'pending') {
            logEntry.style.backgroundColor = 'rgba(253, 126, 20, 0.1)';
            logEntry.style.borderLeft = '4px solid #fd7e14';
        }
        
        let icon = '';
        switch(type) {
            case 'success': icon = '<i class="fas fa-check-circle"></i>'; break;
            case 'error': icon = '<i class="fas fa-times-circle"></i>'; break;
            case 'warning': icon = '<i class="fas fa-exclamation-triangle"></i>'; break;
            case 'pending': icon = '<i class="fas fa-clock"></i>'; break;
            default: icon = '<i class="fas fa-info-circle"></i>';
        }
        
        logEntry.innerHTML = `
            <span class="log-timestamp">[${timestamp}]</span>
            ${icon} ${message}
        `;
        
        this.output.appendChild(logEntry);
        this.scrollToBottom();
    }

    scrollToBottom() {
        this.output.parentElement.scrollTop = this.output.parentElement.scrollHeight;
    }

    clear() {
        this.output.innerHTML = '';
    }

    // Poll for payment callbacks and backend logs
    startCallbackPolling() {
        setInterval(async () => {
            try {
                // Poll for callbacks
                const callbackResponse = await fetch('/api/payment/test/callbacks/recent', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (callbackResponse.ok) {
                    const data = await callbackResponse.json();
                    if (data.success && data.callbacks && data.callbacks.length > 0) {
                        // Process new callbacks
                        data.callbacks.forEach(callback => {
                            const callbackTime = new Date(callback.timestamp).getTime();
                            if (callbackTime > this.lastCallbackCheck) {
                                this.displayCallback(callback);
                            }
                        });
                        this.lastCallbackCheck = Date.now();
                    }
                }

                // Poll for recent backend logs (payment-related only)
                const logResponse = await fetch('/api/logs/recent?payment_only=1', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (logResponse.ok) {
                    const logData = await logResponse.json();
                    if (logData.success && logData.logs && logData.logs.length > 0) {
                        // Display recent backend logs (limit to avoid spam)
                        const recentLogs = logData.logs.slice(-5); // Only show last 5 logs
                        recentLogs.forEach(log => {
                            this.displayBackendLog(log);
                        });
                    }
                }
            } catch (error) {
                // Silently handle polling errors to avoid spam
                console.debug('Polling error:', error);
            }
        }, 5000); // Poll every 5 seconds
    }

    // Display payment provider callback
    displayCallback(callback) {
        const timestamp = new Date(callback.timestamp).toLocaleString('fr-FR');
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry log-${callback.level} callback-entry`;
        
        // Special styling for success callbacks
        if (callback.isSuccess) {
            logEntry.style.backgroundColor = 'rgba(0, 255, 0, 0.15)';
            logEntry.style.borderLeft = '5px solid #00ff00';
            logEntry.style.animation = 'pulse-success 2s ease-in-out';
        }
        
        let icon = '📞';
        switch(callback.level) {
            case 'success': icon = '✅'; break;
            case 'error': icon = '❌'; break;
            case 'warning': icon = '⚠️'; break;
            default: icon = '📞';
        }
        
        logEntry.innerHTML = `
            <span class="log-timestamp">[${timestamp}]</span>
            <span class="callback-badge">[CALLBACK ${callback.provider}]</span>
            ${icon} ${callback.message}
        `;
        
        // Add details if available
        if (callback.details) {
            const details = Object.entries(callback.details)
                .filter(([key, value]) => value !== 'N/A' && value !== null)
                .map(([key, value]) => `${key}: ${value}`)
                .join(' | ');
            
            if (details) {
                logEntry.innerHTML += `<br><span class="callback-details">   💳 ${details}</span>`;
            }
        }
        
        this.output.appendChild(logEntry);
        this.output.scrollTop = this.output.scrollHeight;
        
        // Update statistics for successful callbacks
        if (callback.isSuccess) {
            updateStats('success');
        }
    }

    // Display backend logs from Laravel
    displayBackendLog(log) {
        // Avoid duplicating logs that are already shown via API response
        const existingLogs = Array.from(this.output.children).map(el => el.textContent);
        const logText = log.message;
        
        if (existingLogs.some(existing => existing.includes(logText.substring(0, 50)))) {
            return; // Skip if similar log already exists
        }

        const timestamp = new Date(log.timestamp).toLocaleString('fr-FR');
        
        // Auto-detect PENDING status in backend logs
        let logLevel = log.level;
        if (logText.includes('PENDING') && logLevel !== 'error') {
            logLevel = 'pending';
        }
        
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry log-${logLevel} backend-log`;
        
        // Special styling for backend logs
        if (logLevel === 'pending') {
            logEntry.style.backgroundColor = 'rgba(253, 126, 20, 0.1)';
            logEntry.style.borderLeft = '3px solid #fd7e14';
        } else {
            logEntry.style.backgroundColor = 'rgba(100, 149, 237, 0.1)';
            logEntry.style.borderLeft = '3px solid #6495ED';
        }
        
        let icon = '🔧';
        switch(logLevel) {
            case 'success': icon = '✅'; break;
            case 'error': icon = '❌'; break;
            case 'warning': icon = '⚠️'; break;
            case 'pending': icon = '🕐'; break;
            case 'info': icon = 'ℹ️'; break;
            default: icon = '🔧';
        }
        
        logEntry.innerHTML = `
            <span class="log-timestamp">[${timestamp}]</span>
            <span class="backend-badge">[BACKEND]</span>
            ${icon} ${log.message}
        `;
        
        this.output.appendChild(logEntry);
        this.scrollToBottom();
    }
}

// Initialize console
const console = new PaymentConsole();

// Form submission handler
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    
    const formData = {
        provider: document.getElementById('paymentProvider').value,
        phone: document.getElementById('phoneNumber').value,
        amount: document.getElementById('amount').value,
        mode: document.getElementById('testMode').value
    };
    
    if (!validateForm(formData)) {
        // Re-enable button on validation error
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
        return;
    }
    
    try {
        await initiatePayment(formData);
    } finally {
        // Re-enable button after processing
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    }
});

// Form validation - minimal logging
function validateForm(data) {
    if (!data.provider) {
        console.log('❌ Veuillez sélectionner un fournisseur de paiement', 'error');
        updateStats('error');
        return false;
    }
    
    if (!data.phone) {
        console.log('❌ Veuillez entrer un numéro de téléphone', 'error');
        updateStats('error');
        return false;
    }
    
    if (!data.amount || data.amount < 10) {
        console.log('❌ Le montant doit être supérieur à 10 FCFA', 'error');
        updateStats('error');
        return false;
    }
    
    return true;
}

// Main payment initiation function
async function initiatePayment(data) {
    const transactionId = generateTransactionId();
    const providerName = data.provider.toUpperCase();
    
    // Only essential logging - payment initiated
    console.log(`� ${providerName} payment initiated`, 'info');
    
    try {
        updateStats('pending');
        
        // Call actual API endpoint
        const endpoint = PAYMENT_CONFIG.endpoints[data.provider];
        const requestData = {
            phone_number: data.phone,
            amount: parseInt(data.amount),
            test_mode: data.mode,
            external_id: transactionId
        };
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        });
        
        const result = await response.json();
        
        // Display backend logs only (main source of information)
        if (result.logs && Array.isArray(result.logs)) {
            result.logs.forEach(log => {
                console.log(log.message, log.level);
            });
        }
        
        if (response.ok && result.success) {
            console.log(`✅ Payment completed: ${result.status || 'SUCCESS'}`, 'success');
            
            // Track transaction for status checking
            if (result.reference_id || result.external_id) {
                addTransactionToDropdown(result, data);
            }
            
            updateStats('success');
            updateStats('pending', -1);
        } else {
            const errorMsg = result.message || result.error || 'Payment failed';
            console.log(`❌ ${errorMsg}`, 'error');
            
            updateStats('error');
            updateStats('pending', -1);
        }
        
    } catch (error) {
        console.log(`❌ Network error: ${error.message}`, 'error');
        updateStats('error');
        updateStats('pending', -1);
    }
}

// Detect test scenario based on phone number
function detectTestScenario(provider, phone) {
    const testNumbers = PAYMENT_CONFIG.testNumbers[provider];
    
    if (testNumbers.success === phone) return 'success';
    if (testNumbers.pending === phone) return 'pending';
    if (testNumbers.failure === phone) return 'failure';
    
    // Random scenario for non-test numbers
    const scenarios = ['success', 'failure', 'pending'];
    return scenarios[Math.floor(Math.random() * scenarios.length)];
}

// Simulate payment API response
async function simulatePaymentResponse(scenario, transactionId, data) {
    switch (scenario) {
        case 'success':
            return {
                success: true,
                transactionId: transactionId,
                confirmationCode: generateConfirmationCode(),
                status: 'completed',
                message: 'Paiement traité avec succès'
            };
            
        case 'failure':
            const errors = [
                'Solde insuffisant',
                'Numéro invalide', 
                'Service temporairement indisponible',
                'Transaction refusée par l\'opérateur'
            ];
            return {
                success: false,
                error: errors[Math.floor(Math.random() * errors.length)],
                errorCode: `ERR_${Math.floor(Math.random() * 9999).toString().padStart(4, '0')}`,
                status: 'failed'
            };
            
        case 'pending':
            // Simulate pending then eventual success/failure
            setTimeout(async () => {
                const finalResult = Math.random() > 0.3; // 70% success rate
                if (finalResult) {
                    console.log(`✅ Transaction ${transactionId} confirmée après vérification`, 'success');
                    updateStats('success');
                } else {
                    console.log(`❌ Transaction ${transactionId} échouée après timeout`, 'error');
                    updateStats('error');
                }
                updateStats('pending', -1);
            }, 5000);
            
            return {
                success: true,
                transactionId: transactionId,
                status: 'pending',
                message: 'Transaction en attente de confirmation'
            };
    }
}

// Utility functions
function generateTransactionId() {
    return 'TXN_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5).toUpperCase();
}

function generateConfirmationCode() {
    return Math.random().toString(36).substr(2, 8).toUpperCase();
}

function updateStats(type, delta = 1) {
    stats[type] = Math.max(0, stats[type] + delta);
    document.getElementById(type + 'Count').textContent = stats[type];
}

function clearLogs() {
    console.clear();
    // Reset stats
    stats = { success: 0, pending: 0, error: 0 };
    updateStats('success', 0);
    updateStats('pending', 0);
    updateStats('error', 0);
}

function refreshLogs() {
    // Silent refresh - no console spam
    const statusIndicator = document.querySelector('.status-indicator');
    if (statusIndicator) {
        statusIndicator.className = 'status-indicator status-success';
        statusIndicator.parentElement.innerHTML = `
            <span class="status-indicator status-success"></span>
            Connecté - Actualisé
        `;
        
        // Reset to normal after 2 seconds
        setTimeout(() => {
            statusIndicator.parentElement.innerHTML = `
                <span class="status-indicator status-success"></span>
                Connecté
            `;
        }, 2000);
    }
}

// Test number helpers
function fillTestNumber(provider, scenario) {
    const testNumbers = PAYMENT_CONFIG.testNumbers[provider];
    const phoneInput = document.getElementById('phoneNumber');
    const providerSelect = document.getElementById('paymentProvider');
    
    providerSelect.value = provider;
    phoneInput.value = testNumbers[scenario];
    
    // Silent fill - no console logs
}

// Transaction management functions
function addTransactionToDropdown(result, requestData) {
    const transaction = {
        id: result.reference_id || result.external_id || generateTransactionId(),
        provider: requestData.provider,
        phone: requestData.phone,
        amount: requestData.amount,
        status: result.status || 'PENDING',
        timestamp: new Date().toISOString(),
        reference_id: result.reference_id,
        external_id: result.external_id
    };
    
    // Add to beginning of array (most recent first)
    recentTransactions.unshift(transaction);
    
    // Keep only last 10 transactions
    if (recentTransactions.length > 10) {
        recentTransactions = recentTransactions.slice(0, 10);
    }
    
    updateTransactionDropdown();
}

function updateTransactionDropdown() {
    const select = document.getElementById('transactionSelect');
    if (!select) return;
    
    // Clear existing options except the first one
    select.innerHTML = '<option value="">Sélectionner une transaction...</option>';
    
    recentTransactions.forEach(transaction => {
        const option = document.createElement('option');
        option.value = JSON.stringify(transaction);
        
        const displayText = `${transaction.provider.toUpperCase()} - ${transaction.phone} - ${transaction.amount}F - ${transaction.status}`;
        option.textContent = displayText;
        
        select.appendChild(option);
    });
}

// Check transaction status
async function checkTransactionStatus() {
    const select = document.getElementById('transactionSelect');
    const selectedValue = select.value;
    
    if (!selectedValue) {
        console.log('❌ Please select a transaction to check', 'error');
        return;
    }
    
    try {
        const transaction = JSON.parse(selectedValue);
        console.log(`🔍 Checking status for transaction: ${transaction.id}`, 'info');
        
        // Call the appropriate status endpoint
        const endpoint = `/api/payment/test/${transaction.provider}/status/${transaction.reference_id || transaction.external_id}`;
        
        const response = await fetch(endpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            
            if (result.success) {
                console.log(`✅ Status: ${result.status} | Transaction: ${transaction.id}`, 'success');
                
                // Update transaction in list if status changed
                const txIndex = recentTransactions.findIndex(tx => tx.id === transaction.id);
                if (txIndex !== -1) {
                    recentTransactions[txIndex].status = result.status;
                    updateTransactionDropdown();
                }
            } else {
                console.log(`❌ Status check failed: ${result.message}`, 'error');
            }
        } else {
            console.log(`❌ Status check request failed: HTTP ${response.status}`, 'error');
        }
        
    } catch (error) {
        console.log(`❌ Status check error: ${error.message}`, 'error');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize transaction dropdown
    updateTransactionDropdown();
    
    // Console starts clean - ready for backend logs only
});

// Handle provider selection - minimal logging only
document.getElementById('paymentProvider').addEventListener('change', function() {
    // Provider selected - no console spam, just functionality
});