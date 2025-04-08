export function useNotification() {
    
    const success = (message) => {
        alert(message);
    };
    
    const error = (message, errors = null) => {
        alert(message);
        
        if (errors) {
            console.error('Errors:', errors);
        }
    };
    
    const confirm = (message = 'Are you sure you want to proceed?') => {
        return window.confirm(message);
    };

    return {
        success,
        error,
        confirm
    };
} 