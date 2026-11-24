import { Head, useForm } from '@inertiajs/react';
import { CalendarDays, Plus } from 'lucide-react';
import { useState } from 'react';
import SlotController from '@/actions/App/Http/Controllers/Monitoring/SlotController';
import { SlotForm } from '@/components/monitoring/slot-form';
import type { MonitorSlot, SlotGroup } from '@/components/monitoring/slot-form';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Paginated } from '@/types';

function dateLabel(value: string) {
    const [year, month, day] = value.slice(0, 10).split('-');
    return `${day}/${month}/${year}`;
}

export default function SlotsIndex({
    groups,
    slots,
}: {
    groups: SlotGroup[];
    slots: Paginated<MonitorSlot>;
}) {
    const [editing, setEditing] = useState<MonitorSlot | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const [cancelling, setCancelling] = useState<MonitorSlot | null>(null);
    const cancelForm = useForm({});

    function openForm(slot: MonitorSlot | null) {
        setEditing(slot);
        setFormOpen(true);
    }

    function cancelSlot() {
        if (cancelling) {
            cancelForm.submit(SlotController.destroy(cancelling.id), {
                preserveScroll: true,
                onSuccess: () => setCancelling(null),
            });
        }
    }

    return (
        <>
            <Head title="Meus horários" />
            <div className="flex flex-col gap-6 p-4 md:px-11">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            Meus horários
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Publique e gerencie seus horários de monitoria.
                        </p>
                    </div>
                    {groups.length > 0 && (
                        <Button
                            onClick={() => openForm(null)}
                            className="min-h-11"
                        >
                            <Plus />
                            Publicar horário
                        </Button>
                    )}
                </header>

                {slots.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border bg-card px-6 py-12 text-center">
                        <CalendarDays
                            className="size-8 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <p className="font-medium">Nenhum horário publicado</p>
                    </div>
                ) : (
                    <ul className="overflow-hidden rounded-xl border bg-card">
                        <li
                            aria-hidden="true"
                            className="hidden grid-cols-[1.3fr_1.5fr_1.25fr_.45fr_.8fr_1.2fr] gap-3 border-b bg-muted/60 px-4 py-3 text-sm font-medium lg:grid"
                        >
                            <span>Dia e horário</span>
                            <span>Turma</span>
                            <span>Local</span>
                            <span>Vagas</span>
                            <span>Status</span>
                            <span>Ações</span>
                        </li>
                        {slots.data.map((slot) => (
                            <li
                                key={slot.id}
                                className="grid gap-3 border-b px-4 py-4 text-sm last:border-b-0 sm:grid-cols-2 lg:grid-cols-[1.3fr_1.5fr_1.25fr_.45fr_.8fr_1.2fr] lg:items-center"
                            >
                                <div>
                                    <span className="font-medium">
                                        {dateLabel(slot.starts_at)}
                                    </span>
                                    <div className="text-muted-foreground">
                                        {slot.starts_at.slice(11)} –{' '}
                                        {slot.ends_at.slice(0, 10) !==
                                            slot.starts_at.slice(0, 10) &&
                                            `${dateLabel(slot.ends_at)} `}
                                        {slot.ends_at.slice(11)}
                                    </div>
                                </div>
                                <span>{slot.group}</span>
                                <span className="break-all">
                                    {slot.location}
                                </span>
                                <span>
                                    {slot.capacity}
                                    <span className="lg:sr-only"> vagas</span>
                                </span>
                                <Badge
                                    variant={
                                        slot.cancelled ? 'secondary' : 'outline'
                                    }
                                >
                                    {slot.cancelled ? 'Cancelado' : 'Publicado'}
                                </Badge>
                                <div className="flex flex-wrap gap-1">
                                    {slot.editable && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="min-h-11"
                                            onClick={() => openForm(slot)}
                                        >
                                            Editar
                                        </Button>
                                    )}
                                    {!slot.cancelled && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="min-h-11 text-destructive"
                                            onClick={() => setCancelling(slot)}
                                        >
                                            Cancelar
                                        </Button>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                <Pagination page={slots} label="horários" />
            </div>
            <Dialog open={formOpen} onOpenChange={setFormOpen}>
                <DialogContent className="max-h-[90dvh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>
                            {editing ? 'Editar horário' : 'Publicar horário'}
                        </DialogTitle>
                        <DialogDescription>
                            Informe quando, onde e quantas pessoas você pode
                            atender.
                        </DialogDescription>
                    </DialogHeader>
                    {formOpen && (
                        <SlotForm
                            groups={groups}
                            slot={editing}
                            onClose={() => setFormOpen(false)}
                        />
                    )}
                </DialogContent>
            </Dialog>
            <Dialog
                open={cancelling !== null}
                onOpenChange={(open) => {
                    if (!open) setCancelling(null);
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancelar horário?</DialogTitle>
                        <DialogDescription>
                            {cancelling?.group} ·{' '}
                            {cancelling && dateLabel(cancelling.starts_at)} às{' '}
                            {cancelling?.starts_at.slice(11)}. O horário ficará
                            indisponível.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setCancelling(null)}
                            className="min-h-11"
                        >
                            Voltar
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={cancelSlot}
                            disabled={cancelForm.processing}
                            className="min-h-11"
                        >
                            Cancelar horário
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

SlotsIndex.layout = {
    breadcrumbs: [{ title: 'Meus horários', href: SlotController.index() }],
};
