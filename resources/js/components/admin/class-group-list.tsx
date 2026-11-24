import { Button } from '@/components/ui/button';
import type { AdminClassGroup } from '@/types';

const professorList = new Intl.ListFormat('pt-BR', {
    style: 'long',
    type: 'conjunction',
});

const professorNames = (classGroup: AdminClassGroup) =>
    professorList.format(
        classGroup.professors.map((professor) => professor.name),
    );

const courseLabel = (classGroup: AdminClassGroup) =>
    `${classGroup.course.code} · ${classGroup.course.name}`;

type Props = {
    classGroups: AdminClassGroup[];
    onEdit: (classGroup: AdminClassGroup) => void;
};

export function ClassGroupList({ classGroups, onEdit }: Props) {
    return (
        <>
            <ul className="flex flex-col gap-3 md:hidden">
                {classGroups.map((classGroup) => (
                    <li
                        key={classGroup.id}
                        className="flex flex-col gap-3 rounded-xl border bg-card p-4"
                    >
                        <div className="flex items-center justify-between gap-2">
                            <span className="font-semibold">
                                {classGroup.course.code} · {classGroup.number}
                            </span>
                            <span className="text-sm text-muted-foreground">
                                {classGroup.semester}
                            </span>
                        </div>
                        <p className="text-sm">{classGroup.course.name}</p>
                        <dl className="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-sm">
                            <dt className="text-muted-foreground">
                                Professores
                            </dt>
                            <dd>{professorNames(classGroup)}</dd>
                            <dt className="text-muted-foreground">Horário</dt>
                            <dd>{classGroup.schedule}</dd>
                        </dl>
                        <Button
                            variant="outline"
                            className="h-11 w-full"
                            onClick={() => onEdit(classGroup)}
                        >
                            Editar turma
                        </Button>
                    </li>
                ))}
            </ul>

            <div className="hidden md:block">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left text-xs text-muted-foreground uppercase">
                        <tr>
                            <th className="px-4 py-3 font-medium">
                                Disciplina
                            </th>
                            <th className="px-4 py-3 font-medium">Turma</th>
                            <th className="px-4 py-3 font-medium">Semestre</th>
                            <th className="px-4 py-3 font-medium">
                                Professores
                            </th>
                            <th className="px-4 py-3 font-medium">Horário</th>
                            <th className="px-4 py-3 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {classGroups.map((classGroup) => (
                            <tr key={classGroup.id}>
                                <td className="px-4 py-4 font-semibold">
                                    {courseLabel(classGroup)}
                                </td>
                                <td className="px-4 py-4">
                                    {classGroup.number}
                                </td>
                                <td className="px-4 py-4">
                                    {classGroup.semester}
                                </td>
                                <td className="px-4 py-4">
                                    {professorNames(classGroup)}
                                </td>
                                <td className="px-4 py-4">
                                    {classGroup.schedule}
                                </td>
                                <td className="px-4 py-4">
                                    <Button
                                        variant="link"
                                        className="h-auto p-0 font-semibold"
                                        onClick={() => onEdit(classGroup)}
                                    >
                                        Editar turma
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
