/**
 * WK CRM - API Connection Handler
 * Conecta o AdminLTE com a API Laravel
 */

const API_BASE_URL = 'http://localhost:8080/api';

class WKCrmAPI {
    constructor() {
        this.baseURL = API_BASE_URL;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Customer endpoints - Demo mode with fallback to simulation
    async getCustomers() {
        try {
            return await this.request('/customers');
        } catch (error) {
            console.warn('API não disponível, usando dados simulados:', error.message);
            // Return simulated data that matches Laravel API structure
            return {
                data: [
                    {
                        id: 1,
                        name: "João Silva Santos",
                        email: "joao.silva@email.com",
                        phone: "(11) 99999-1111",
                        created_at: "2024-10-15T10:30:00.000000Z",
                        updated_at: "2024-10-15T10:30:00.000000Z"
                    },
                    {
                        id: 2,
                        name: "Maria Oliveira Lima",
                        email: "maria.oliveira@email.com",
                        phone: "(11) 88888-2222",
                        created_at: "2024-10-14T14:20:00.000000Z",
                        updated_at: "2024-10-14T14:20:00.000000Z"
                    },
                    {
                        id: 3,
                        name: "Pedro Costa Ferreira",
                        email: "pedro.costa@email.com",
                        phone: "(11) 77777-3333",
                        created_at: "2024-10-13T09:15:00.000000Z",
                        updated_at: "2024-10-13T09:15:00.000000Z"
                    },
                    {
                        id: 4,
                        name: "Ana Souza Almeida",
                        email: "ana.souza@email.com",
                        phone: "(11) 66666-4444",
                        created_at: "2024-10-12T16:45:00.000000Z",
                        updated_at: "2024-10-12T16:45:00.000000Z"
                    },
                    {
                        id: 5,
                        name: "Carlos Mendes Rocha",
                        email: "carlos.mendes@email.com",
                        phone: "(11) 55555-5555",
                        created_at: "2024-10-11T11:30:00.000000Z",
                        updated_at: "2024-10-11T11:30:00.000000Z"
                    }
                ],
                meta: {
                    total: 5,
                    current_page: 1,
                    per_page: 10
                }
            };
        }
    }

    async getCustomer(id) {
        return this.request(`/customers/${id}`);
    }

    async createCustomer(customerData) {
        return this.request('/customers', {
            method: 'POST',
            body: JSON.stringify(customerData)
        });
    }

    async updateCustomer(id, customerData) {
        return this.request(`/customers/${id}`, {
            method: 'PUT',
            body: JSON.stringify(customerData)
        });
    }

    async deleteCustomer(id) {
        return this.request(`/customers/${id}`, {
            method: 'DELETE'
        });
    }

    // Dashboard stats
    async getDashboardStats() {
        try {
            const customers = await this.getCustomers();
            return {
                totalCustomers: customers.data ? customers.data.length : 0,
                salesThisMonth: this.calculateMonthlySales(customers),
                activeOpportunities: this.countActiveOpportunities(),
                supportTickets: this.countSupportTickets()
            };
        } catch (error) {
            console.error('Error getting dashboard stats:', error);
            return {
                totalCustomers: 0,
                salesThisMonth: 0,
                activeOpportunities: 0,
                supportTickets: 0
            };
        }
    }

    calculateMonthlySales(customers) {
        // Simular vendas baseado no número de clientes
        return (customers.data?.length || 0) * 1250.50;
    }

    countActiveOpportunities() {
        // Simular oportunidades ativas
        return Math.floor(Math.random() * 50) + 10;
    }

    countSupportTickets() {
        // Simular tickets de suporte
        return Math.floor(Math.random() * 20) + 1;
    }
}

// Instância global da API
const wkAPI = new WKCrmAPI();

// Utility functions para UI
function showLoading(element) {
    if (element) {
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';
    }
}

function hideLoading() {
    // Remove loading indicators
    document.querySelectorAll('.loading').forEach(el => {
        el.classList.remove('loading');
    });
}

function showError(message, container = null) {
    const errorHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    if (container) {
        container.innerHTML = errorHtml;
    } else {
        // Show in default container or create one
        const alertContainer = document.getElementById('alert-container') || document.body;
        alertContainer.insertAdjacentHTML('afterbegin', errorHtml);
    }
}

function showSuccess(message, container = null) {
    const successHtml = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    if (container) {
        container.innerHTML = successHtml;
    } else {
        const alertContainer = document.getElementById('alert-container') || document.body;
        alertContainer.insertAdjacentHTML('afterbegin', successHtml);
    }
}

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Format date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('pt-BR');
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('dashboard-container')) {
        loadDashboardData();
    }
    
    if (document.getElementById('customers-container')) {
        loadCustomersData();
    }
});

async function loadDashboardData() {
    try {
        showLoading(document.getElementById('stats-loading'));
        
        const stats = await wkAPI.getDashboardStats();
        
        // Update dashboard cards
        updateDashboardCard('total-customers', stats.totalCustomers, 'Clientes');
        updateDashboardCard('monthly-sales', formatCurrency(stats.salesThisMonth), 'Vendas do Mês');
        updateDashboardCard('active-opportunities', stats.activeOpportunities, 'Oportunidades Ativas');
        updateDashboardCard('support-tickets', stats.supportTickets, 'Tickets');
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Erro ao carregar dados do dashboard: ' + error.message);
    }
}

function updateDashboardCard(cardId, value, label) {
    const valueElement = document.querySelector(`[data-card="${cardId}"] .card-value`);
    const labelElement = document.querySelector(`[data-card="${cardId}"] .card-label`);
    
    if (valueElement) valueElement.textContent = value;
    if (labelElement) labelElement.textContent = label;
}

async function loadCustomersData() {
    try {
        showLoading(document.getElementById('customers-loading'));
        
        const response = await wkAPI.getCustomers();
        const customers = response.data || [];
        
        renderCustomersTable(customers);
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Erro ao carregar clientes: ' + error.message);
    }
}

function renderCustomersTable(customers) {
    const tableBody = document.getElementById('customers-table-body');
    if (!tableBody) return;
    
    tableBody.innerHTML = customers.map(customer => `
        <tr>
            <td>${customer.id}</td>
            <td>${customer.name}</td>
            <td>${customer.email}</td>
            <td>${customer.phone || 'N/A'}</td>
            <td>${formatDate(customer.created_at)}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editCustomer(${customer.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${customer.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

async function deleteCustomer(id) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
    
    try {
        await wkAPI.deleteCustomer(id);
        showSuccess('Cliente excluído com sucesso!');
        loadCustomersData(); // Reload table
    } catch (error) {
        showError('Erro ao excluir cliente: ' + error.message);
    }
}

function editCustomer(id) {
    // TODO: Implementar modal de edição
    alert(`Editar cliente ID: ${id} - Em desenvolvimento`);
}