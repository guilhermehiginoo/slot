export type CourseSearchResult = {
    code: string;
    name: string;
};

export type CourseDetails = {
    code: string;
    name: string;
    description: string | null;
    workload_hours: number | null;
};

export type CourseClassGroup = {
    id: number;
    number: string;
    schedule: string;
    professors: string[];
    monitors: string[];
};
