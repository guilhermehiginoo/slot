export type CourseSearchResult = {
    code: string;
    name: string;
};

export type CourseDetails = {
    code: string;
    name: string;
};

export type CourseClassGroup = {
    id: number;
    number: string;
    schedule: string;
    professors: string[];
    monitors: string[];
};
