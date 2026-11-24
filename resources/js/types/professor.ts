export type CourseSummary = {
    code: string;
    name: string;
};

export type PersonSummary = {
    id: number;
    name: string;
};

export type TaughtClassGroup = {
    id: number;
    number: string;
    schedule: string;
    course: CourseSummary;
    monitors_count: number;
};

export type ClassGroupDetails = {
    id: number;
    semester: string;
    number: string;
    schedule: string;
    course: CourseSummary;
    professors: PersonSummary[];
};

export type ClassMonitor = {
    id: number;
    name: string;
    appointed_at: string;
};

export type PendingInvitation = {
    id: number;
    student: PersonSummary;
    created_at: string;
};

export type PendingApplication = {
    id: number;
    student: PersonSummary;
    message: string | null;
    created_at: string;
};
