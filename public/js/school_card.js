// JavaScript for School Card functionality

// Toggle section settings visibility
function toggleSectionSettings() {
    const settings = document.getElementById('section-settings');
    if (settings.style.display === 'none') {
        settings.style.display = 'block';
        loadSectionToggles();
    } else {
        settings.style.display = 'none';
    }
}

// Load section toggles
function loadSectionToggles() {
    const togglesContainer = document.getElementById('section-toggles');
    const sections = document.querySelectorAll('.school-section');
    
    togglesContainer.innerHTML = '';
    
    sections.forEach(section => {
        const sectionName = section.dataset.section;
        const isVisible = section.style.display !== 'none';
        
        const toggle = document.createElement('div');
        toggle.className = 'section-toggle';
        toggle.innerHTML = `
            <input type="checkbox" 
                   id="toggle-${sectionName}" 
                   ${isVisible ? 'checked' : ''}
                   onchange="toggleSection('${sectionName}', this.checked)">
            <label for="toggle-${sectionName}">${getSectionLabel(sectionName)}</label>
        `;
        
        togglesContainer.appendChild(toggle);
    });
}

// Get human-readable section label
function getSectionLabel(sectionName) {
    const labels = {
        'basic_info': 'Podstawowe informacje',
        'contact': 'Kontakt',
        'schedule': 'Harmonogram',
        'events': 'Wydarzenia',
        'contracts': 'Umowy',
        'tasks': 'Zadania',
        'notes': 'Notatki',
        'procedures': 'Procedury',
        'afterschool': 'Świetlica'
    };
    
    return labels[sectionName] || sectionName.replace('_', ' ');
}

// Toggle section visibility
async function toggleSection(sectionName, isVisible) {
    const section = document.querySelector(`[data-section="${sectionName}"]`);
    if (section) {
        section.style.display = isVisible ? 'block' : 'none';
    }
    
    // Save preference
    await saveSectionPreferences();
}

// Save section visibility preferences
async function saveSectionPreferences() {
    const sections = document.querySelectorAll('.school-section');
    const visibleSections = [];
    
    sections.forEach(section => {
        if (section.style.display !== 'none') {
            visibleSections.push(section.dataset.section);
        }
    });
    
    const result = await apiCall('/api/preferences.php', 'POST', {
        key: 'school_card_visible_sections',
        value: visibleSections
    });
    
    if (result.success) {
        showNotification('Ustawienia widoku zapisane', 'success');
    }
}

// Show change history modal
async function showChangeHistory(detailId, fieldName) {
    const modal = document.getElementById('history-modal');
    const content = document.getElementById('history-content');
    
    content.innerHTML = '<p>Ładowanie historii zmian...</p>';
    modal.style.display = 'flex';
    
    const result = await apiCall(`/api/change_history.php?detail_id=${detailId}`);
    
    if (result.success && result.data) {
        if (result.data.length === 0) {
            content.innerHTML = '<p class="no-data">Brak historii zmian dla tego pola.</p>';
        } else {
            let html = '<div class="history-list">';
            html += `<h4>Historia zmian: ${fieldName}</h4>`;
            html += '<table class="data-table">';
            html += '<thead><tr><th>Data</th><th>Użytkownik</th><th>Stara wartość</th><th>Nowa wartość</th></tr></thead>';
            html += '<tbody>';
            
            result.data.forEach(change => {
                html += '<tr>';
                html += `<td>${formatDateTime(change.changed_at)}</td>`;
                html += `<td>${change.username || 'N/A'}</td>`;
                html += `<td>${change.old_value ? escapeHtml(change.old_value).substring(0, 50) : '-'}</td>`;
                html += `<td>${change.new_value ? escapeHtml(change.new_value).substring(0, 50) : '-'}</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            content.innerHTML = html;
        }
    } else {
        content.innerHTML = '<p class="alert alert-error">Nie udało się załadować historii zmian.</p>';
    }
}

// Close change history modal
function closeHistoryModal() {
    const modal = document.getElementById('history-modal');
    modal.style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('history-modal');
    if (event.target === modal) {
        closeHistoryModal();
    }
});

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make field editable inline
function makeFieldEditable(detailId, currentValue) {
    const field = document.querySelector(`[data-detail-id="${detailId}"]`);
    if (!field) return;
    
    const originalContent = field.innerHTML;
    const textarea = document.createElement('textarea');
    textarea.value = currentValue || '';
    textarea.style.width = '100%';
    textarea.style.minHeight = '100px';
    textarea.style.padding = '0.5rem';
    textarea.style.border = '2px solid var(--primary-color)';
    textarea.style.borderRadius = '0.25rem';
    
    field.innerHTML = '';
    field.appendChild(textarea);
    textarea.focus();
    
    // Save on blur
    textarea.addEventListener('blur', async function() {
        const newValue = textarea.value;
        
        if (newValue !== currentValue) {
            const result = await apiCall('/api/school_detail.php', 'PUT', {
                id: detailId,
                value: newValue
            });
            
            if (result.success) {
                field.innerHTML = escapeHtml(newValue).replace(/\n/g, '<br>');
                showNotification('Pole zaktualizowane', 'success');
            } else {
                field.innerHTML = originalContent;
                showNotification('Błąd podczas aktualizacji', 'error');
            }
        } else {
            field.innerHTML = originalContent;
        }
    });
    
    // Save on Ctrl+Enter
    textarea.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            textarea.blur();
        }
    });
}

// Add double-click to edit functionality
document.addEventListener('DOMContentLoaded', function() {
    const editableFields = document.querySelectorAll('.info-value.editable');
    
    editableFields.forEach(field => {
        field.addEventListener('dblclick', function() {
            const detailId = this.dataset.detailId;
            const currentValue = this.textContent.trim();
            makeFieldEditable(detailId, currentValue);
        });
    });
});
