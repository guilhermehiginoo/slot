import { router } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { CourseOption } from '@/types';

const allCourses = 'all';

type Filters = {
    course: number | null;
    semester: string;
};

type Props = {
    filters: Filters;
    courses: CourseOption[];
    semesters: string[];
};

export function ClassGroupFilters({ filters, courses, semesters }: Props) {
    const apply = (changed: Partial<Filters>) => {
        const { course, semester } = { ...filters, ...changed };

        router.get(
            ClassGroupController.index.url({
                query: course === null ? { semester } : { course, semester },
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <div className="grid gap-3 md:flex md:gap-4">
            <div className="grid gap-1.5 md:w-80">
                <Label htmlFor="course-filter">Disciplina</Label>
                <Select
                    value={filters.course?.toString() ?? allCourses}
                    onValueChange={(value) =>
                        apply({
                            course: value === allCourses ? null : Number(value),
                        })
                    }
                >
                    <SelectTrigger
                        id="course-filter"
                        className="h-11 w-full bg-card"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={allCourses}>
                            Todas as disciplinas
                        </SelectItem>
                        {courses.map((course) => (
                            <SelectItem
                                key={course.id}
                                value={course.id.toString()}
                            >
                                {course.code} · {course.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid gap-1.5 md:w-40">
                <Label htmlFor="semester-filter">Semestre</Label>
                <Select
                    value={filters.semester}
                    onValueChange={(semester) => apply({ semester })}
                >
                    <SelectTrigger
                        id="semester-filter"
                        className="h-11 w-full bg-card"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {semesters.map((semester) => (
                            <SelectItem key={semester} value={semester}>
                                {semester}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    );
}
