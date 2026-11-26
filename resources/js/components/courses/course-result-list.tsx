import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import CourseController from '@/actions/App/Http/Controllers/CourseController';
import type { CourseSearchResult } from '@/types';

export function CourseResultList({
    courses,
}: {
    courses: CourseSearchResult[];
}) {
    return (
        <ul className="flex flex-col divide-y rounded-xl border bg-card">
            {courses.map((course) => (
                <li key={course.code}>
                    <Link
                        href={CourseController.show(course.code)}
                        className="flex min-h-14 items-center gap-3 px-4 py-3 transition-colors first:rounded-t-xl last:rounded-b-xl hover:bg-accent"
                    >
                        <span className="flex-1">
                            <span className="font-semibold">{course.code}</span>
                            {' – '}
                            {course.name}
                        </span>
                        <ChevronRight className="size-4 shrink-0 text-muted-foreground" />
                    </Link>
                </li>
            ))}
        </ul>
    );
}
