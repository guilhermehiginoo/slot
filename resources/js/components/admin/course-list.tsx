import { CourseActions } from '@/components/admin/course-actions';
import { CourseStatusBadge } from '@/components/admin/course-status-badge';
import type { AdminCourse } from '@/types';

type Props = {
    courses: AdminCourse[];
    onArchive: (course: AdminCourse) => void;
};

export function CourseList({ courses, onArchive }: Props) {
    return (
        <>
            <ul className="flex flex-col gap-3 md:hidden">
                {courses.map((course) => (
                    <li
                        key={course.id}
                        className="flex flex-col gap-3 rounded-xl border p-4"
                    >
                        <div className="flex items-center justify-between gap-2">
                            <span className="font-semibold">{course.code}</span>
                            <CourseStatusBadge course={course} />
                        </div>
                        <p className="text-sm">{course.name}</p>
                        <CourseActions course={course} onArchive={onArchive} />
                    </li>
                ))}
            </ul>

            <div className="hidden md:block">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left text-xs text-muted-foreground uppercase">
                        <tr>
                            <th className="px-4 py-3 font-medium">Código</th>
                            <th className="px-4 py-3 font-medium">Nome</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {courses.map((course) => (
                            <tr key={course.id}>
                                <td className="px-4 py-4 font-semibold">
                                    {course.code}
                                </td>
                                <td
                                    className={
                                        course.archived_at === null
                                            ? 'px-4 py-4'
                                            : 'px-4 py-4 text-muted-foreground'
                                    }
                                >
                                    {course.name}
                                </td>
                                <td className="px-4 py-4">
                                    <CourseStatusBadge course={course} />
                                </td>
                                <td className="px-4 py-4">
                                    <CourseActions
                                        course={course}
                                        onArchive={onArchive}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
