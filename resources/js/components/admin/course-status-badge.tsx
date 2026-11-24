import { Badge } from '@/components/ui/badge';
import type { AdminCourse } from '@/types';

export function CourseStatusBadge({ course }: { course: AdminCourse }) {
    return course.archived_at === null ? (
        <Badge className="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
            Ativa
        </Badge>
    ) : (
        <Badge variant="secondary">Arquivada</Badge>
    );
}
