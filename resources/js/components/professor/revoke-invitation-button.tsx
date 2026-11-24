import { Form } from '@inertiajs/react';
import MonitorInvitationController from '@/actions/App/Http/Controllers/Professor/MonitorInvitationController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';

export function RevokeInvitationButton({
    invitationId,
}: {
    invitationId: number;
}) {
    return (
        <Form
            {...MonitorInvitationController.destroy.form(invitationId)}
            options={{ preserveScroll: true }}
        >
            {({ processing, errors }) => (
                <>
                    <Button
                        type="submit"
                        variant="outline"
                        size="sm"
                        className="h-11"
                        disabled={processing}
                    >
                        Revogar
                    </Button>
                    <InputError message={errors.invitation} />
                </>
            )}
        </Form>
    );
}
