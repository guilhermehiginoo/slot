import { Link } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import { Button } from '@/components/ui/button';
import type { TaughtClassGroup } from '@/types';

const monitorsLabel = (count: number) =>
    count === 1 ? '1 monitor' : `${count} monitores`;

type Props = {
    classGroups: TaughtClassGroup[];
};

export function TaughtClassGroupList({ classGroups }: Props) {
    return (
        <>
            <ul className="grid gap-3 md:grid-cols-2 xl:hidden">
                {classGroups.map((classGroup) => (
                    <li
                        key={classGroup.id}
                        className="flex flex-col gap-3 rounded-xl border bg-card p-4"
                    >
                        <span className="font-semibold">
                            {classGroup.course.code} · {classGroup.number}
                        </span>
                        <p className="text-sm">{classGroup.course.name}</p>
                        <p className="text-sm text-muted-foreground">
                            {classGroup.schedule} ·{' '}
                            {monitorsLabel(classGroup.monitors_count)}
                        </p>
                        <Button
                            variant="outline"
                            className="h-11 w-full"
                            asChild
                        >
                            <Link href={ClassGroupController.show(classGroup)}>
                                Gerenciar turma
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>

            <div className="hidden xl:block">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left text-xs text-muted-foreground uppercase">
                        <tr>
                            <th className="px-4 py-3 font-medium">
                                Disciplina
                            </th>
                            <th className="px-4 py-3 font-medium">Turma</th>
                            <th className="px-4 py-3 font-medium">Horário</th>
                            <th className="px-4 py-3 font-medium">Monitores</th>
                            <th className="px-4 py-3 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {classGroups.map((classGroup) => (
                            <tr key={classGroup.id}>
                                <td className="px-4 py-4 font-semibold">
                                    {classGroup.course.code} ·{' '}
                                    {classGroup.course.name}
                                </td>
                                <td className="px-4 py-4">
                                    {classGroup.number}
                                </td>
                                <td className="px-4 py-4">
                                    {classGroup.schedule}
                                </td>
                                <td className="px-4 py-4">
                                    {classGroup.monitors_count}
                                </td>
                                <td className="px-4 py-4">
                                    <Button variant="outline" asChild>
                                        <Link
                                            href={ClassGroupController.show(
                                                classGroup,
                                            )}
                                        >
                                            Gerenciar turma
                                        </Link>
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
