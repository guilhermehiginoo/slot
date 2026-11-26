const dateFormat = new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

export function formatDate(value: string): string {
    return dateFormat.format(new Date(value));
}
