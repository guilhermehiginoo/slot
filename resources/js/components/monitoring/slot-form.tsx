import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import SlotController from '@/actions/App/Http/Controllers/Monitoring/SlotController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type SlotGroup = { id: number; label: string };

export type MonitorSlot = {
    id: number;
    group: string;
    group_id: number;
    starts_at: string;
    ends_at: string;
    location: string;
    capacity: number;
    cancelled: boolean;
    editable: boolean;
};

const fields = [
    { name: 'starts_at', label: 'Início', type: 'datetime-local' },
    { name: 'ends_at', label: 'Fim', type: 'datetime-local' },
    { name: 'location', label: 'Local ou link online', type: 'text' },
    { name: 'capacity', label: 'Vagas', type: 'number' },
] as const;

export function SlotForm({
    groups,
    slot,
    onClose,
}: {
    groups: SlotGroup[];
    slot: MonitorSlot | null;
    onClose: () => void;
}) {
    const form = useForm({
        classGroup: slot?.group_id ?? groups[0]?.id ?? 0,
        starts_at: slot?.starts_at ?? '',
        ends_at: slot?.ends_at ?? '',
        location: slot?.location ?? '',
        capacity: slot ? String(slot.capacity) : '1',
        repeat: false,
        weeks: '2',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const { starts_at, ends_at, location, capacity, repeat, weeks } =
            form.data;
        const data = { starts_at, ends_at, location, capacity };

        form.transform(() => (slot || !repeat ? data : { ...data, weeks }));
        form.submit(
            slot
                ? SlotController.update(slot.id)
                : SlotController.store(form.data.classGroup),
            { preserveScroll: true, onSuccess: onClose },
        );
    }

    return (
        <form onSubmit={submit} className="space-y-4">
            <div className="space-y-2">
                <Label htmlFor="slot-group">Turma</Label>
                <select
                    id="slot-group"
                    className="h-11 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring"
                    value={form.data.classGroup}
                    onChange={(event) =>
                        form.setData('classGroup', Number(event.target.value))
                    }
                    disabled={slot !== null}
                    required
                >
                    {groups.map((group) => (
                        <option key={group.id} value={group.id}>
                            {group.label}
                        </option>
                    ))}
                </select>
            </div>
            <p className="text-sm text-muted-foreground">
                Datas e horários no fuso de Natal.
            </p>
            <div className="grid gap-4 sm:grid-cols-2">
                {fields.map(({ name, label, type }) => (
                    <div key={name} className="min-w-0 space-y-2">
                        <Label htmlFor={name}>{label}</Label>
                        <Input
                            id={name}
                            type={type}
                            value={form.data[name]}
                            onChange={(event) =>
                                form.setData(name, event.target.value)
                            }
                            min={type === 'number' ? 1 : undefined}
                            maxLength={type === 'text' ? 255 : undefined}
                            aria-invalid={Boolean(form.errors[name])}
                            aria-describedby={
                                form.errors[name] ? `${name}-error` : undefined
                            }
                            className="h-11 min-w-0"
                            required
                        />
                        <InputError
                            id={`${name}-error`}
                            message={form.errors[name]}
                        />
                    </div>
                ))}
            </div>
            {!slot && (
                <div className="space-y-3">
                    <div className="flex min-h-11 items-center gap-2">
                        <Checkbox
                            id="slot-repeat"
                            checked={form.data.repeat}
                            onCheckedChange={(checked) =>
                                form.setData('repeat', checked === true)
                            }
                        />
                        <Label htmlFor="slot-repeat">
                            Repetir semanalmente
                        </Label>
                    </div>
                    {form.data.repeat && (
                        <div className="space-y-2">
                            <Label htmlFor="slot-weeks">Total de semanas</Label>
                            <Input
                                id="slot-weeks"
                                type="number"
                                min={2}
                                max={52}
                                value={form.data.weeks}
                                onChange={(event) =>
                                    form.setData('weeks', event.target.value)
                                }
                                aria-invalid={Boolean(form.errors.weeks)}
                                aria-describedby={
                                    form.errors.weeks
                                        ? 'slot-weeks-error'
                                        : undefined
                                }
                                className="h-11"
                                required
                            />
                            <InputError
                                id="slot-weeks-error"
                                message={form.errors.weeks}
                            />
                        </div>
                    )}
                </div>
            )}
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    onClick={onClose}
                    className="min-h-11"
                >
                    Voltar
                </Button>
                <Button
                    type="submit"
                    disabled={form.processing}
                    className="min-h-11"
                >
                    {slot ? 'Salvar alterações' : 'Publicar horário'}
                </Button>
            </DialogFooter>
        </form>
    );
}
