import type { MonitorInvitationStatus } from '@/types';

export const invitationStatusLabels: Record<MonitorInvitationStatus, string> = {
    pending: 'Pendente',
    accepted: 'Aceito',
    declined: 'Recusado',
    revoked: 'Revogado',
};
