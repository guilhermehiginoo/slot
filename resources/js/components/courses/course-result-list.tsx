import type { CourseSearchResult } from '@/types';

export function CourseResultList({
    courses,
}: {
    courses: CourseSearchResult[];
}) {
    return (
        <ul className="flex flex-col divide-y rounded-xl border bg-card">
            {courses.map((course) => (
                <li
                    key={course.code}
                    className="flex min-h-14 items-center px-4 py-3"
                >
                    <span>
                        <span className="font-semibold">{course.code}</span>
                        {' – '}
                        {course.name}
                    </span>
                </li>
            ))}
        </ul>
    );
}
