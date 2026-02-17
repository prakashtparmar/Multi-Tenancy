
        isStatusValid(targetStatus) {
            if (this.selected.length === 0) return false;
            
            // Get statuses of selected orders from DOM to ensure they are up-to-date (even after AJAX)
            const selectedStatuses = this.selected.map(id => {
                const checkbox = document.querySelector(`input[type='checkbox'][value='${id}']`);
                return checkbox ? checkbox.getAttribute('data-status') : null;
            }).filter(s => s !== null);

            if (selectedStatuses.length === 0) return false;

            // Handle cancellation logic
            if (targetStatus === 'cancelled') {
                 // Can cancel if not delivered or already cancelled
                 return selectedStatuses.every(current => current !== 'delivered' && current !== 'cancelled');
            }

            const targetIndex = this.statusFlow.indexOf(targetStatus);
            if (targetIndex === -1) return false;

            // Forward transition: Target must be strictly greater than current for AT LEAST ONE selected
            return selectedStatuses.some(current => {
                // Normalize 'completed' to 'delivered' for logic
                let normalizedCurrent = current === 'completed' ? 'delivered' : current;
                
                const currentIndex = this.statusFlow.indexOf(normalizedCurrent);
                
                // If current status is unknown or invalid for flow, block this specific order
                if (currentIndex === -1) return false; 
                
                // Check if this specific order can move to target
                return targetIndex > currentIndex;
            });
        }
