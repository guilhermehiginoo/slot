import type { CourseSummary } from './professor';

export type MonitorInvitationStatus =
    | 'pending'
    | 'accepted'
    | 'declined'
    | 'revoked';

export type StudentInvitation = {
    id: number;
    classGroup: {
        number: string;
        course: CourseSummary;
    };
    invitedBy: string;
    created_at: string;
};

export type MonitorCandidate = {
    id: number;
    name: string;
    email: string;
};
