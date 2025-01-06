export function formatCurrency(value) {
    return Intl.NumberFormat('en-US', {}).format(value);
}
