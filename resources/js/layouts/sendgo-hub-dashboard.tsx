import { Link } from '@inertiajs/react';
import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import type { LayoutComponentProps } from '@cms-orbit/core/contract';
import { useT } from '@cms-orbit/core/lib/i18n';
import { Banner } from '@cms-orbit/core/ui/banner';
import { Card, CardBody, CardHeader } from '@cms-orbit/core/ui/card';
import { EmptyState } from '@cms-orbit/core/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeaderCell, TableRow } from '@cms-orbit/core/ui/table';

interface HubMessage {
    channel: string;
    message_type: string;
    management_code: string;
    status: string;
    total_count: number;
    success_count: number;
    failed_count: number;
    created_at: string;
    url: string;
}

interface HubSender {
    alias: string;
    number: string;
    status: string;
    type: string;
}

interface HubProfile {
    name: string;
    yellow_id: string;
    status: string;
    sender_key: string;
}

interface HubTemplate {
    template_code: string;
    template_name: string;
    status: string;
    inspection_status: string;
    synced_at: string;
    url: string;
}

interface ChartDataset {
    name?: string;
    labels?: string[];
    values?: number[];
}

interface HubPayload {
    needsSetup?: boolean;
    settingsUrl?: string;
    connectionError?: string | null;
    recentMessages?: HubMessage[];
    messageTypes?: ChartDataset[];
    senders?: HubSender[];
    kakaoProfiles?: HubProfile[];
    templates?: HubTemplate[];
    links?: {
        templates?: string;
        senders?: string;
        profiles?: string;
    };
}

const CHART_COLORS = ['#10b981', '#6366f1', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6', '#64748b'];

function asHub(value: unknown): HubPayload {
    return value && typeof value === 'object' ? (value as HubPayload) : {};
}

function chartRows(datasets: ChartDataset[]) {
    const [dataset] = datasets;

    if (!dataset?.labels?.length) {
        return [];
    }

    const values = dataset.values ?? [];
    const total = values.reduce((sum, value) => sum + value, 0);

    return dataset.labels.map((label, index) => ({
        name: label,
        value: values[index] ?? 0,
        percent: total > 0 ? Math.round(((values[index] ?? 0) / total) * 100) : 0,
    }));
}

function StatusBadge({ value }: { value: string }) {
    return (
        <span className="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
            {value}
        </span>
    );
}

/** SendGo hub dashboard: recent campaigns, channel mix, senders, profiles, templates. */
export function SendgoHubDashboardLayout({ data }: LayoutComponentProps) {
    const t = useT();
    const hub = asHub(data.hub);
    const chartData = chartRows(hub.messageTypes ?? []);
    const recentMessages = hub.recentMessages ?? [];
    const senders = hub.senders ?? [];
    const profiles = hub.kakaoProfiles ?? [];
    const templates = hub.templates ?? [];
    const apiEmptyHeading = hub.needsSetup
        ? t('Connect SendGo API credentials to load delivery data.')
        : null;

    return (
        <div className="space-y-6">
            {hub.needsSetup ? (
                <Banner tone="warning" title={t('SendGo API credentials are required.')}>
                    <p className="mt-1 text-sm">
                        {t('Configure your SendGo access key and secret key to load delivery data.')}
                    </p>
                    <div className="mt-3">
                        <Link
                            href={hub.settingsUrl ?? '#'}
                            className="inline-flex items-center justify-center rounded-lg bg-orbit-primary-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-orbit-primary-500"
                        >
                            {t('Open SendGo settings')}
                        </Link>
                    </div>
                </Banner>
            ) : null}

            {!hub.needsSetup && hub.connectionError ? (
                <Banner tone="danger" title={t('Could not load SendGo data.')}>
                    <p className="mt-1 text-sm">{hub.connectionError}</p>
                    <div className="mt-3">
                        <Link
                            href={hub.settingsUrl ?? '#'}
                            className="inline-flex items-center justify-center rounded-lg border px-3.5 py-2 text-sm font-medium shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            {t('Review SendGo settings')}
                        </Link>
                    </div>
                </Banner>
            ) : null}

            <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader title={t('Recent messages')} />
                    <CardBody className="p-0">
                        {recentMessages.length === 0 ? (
                            <div className="p-6">
                                <EmptyState
                                    icon="bs.chat-dots"
                                    heading={apiEmptyHeading ?? t('No recent messages.')}
                                />
                            </div>
                        ) : (
                            <Table>
                                <TableHead>
                                    <TableRow>
                                        <TableHeaderCell>{t('Channel')}</TableHeaderCell>
                                        <TableHeaderCell>{t('Type')}</TableHeaderCell>
                                        <TableHeaderCell>{t('Code')}</TableHeaderCell>
                                        <TableHeaderCell>{t('Status')}</TableHeaderCell>
                                        <TableHeaderCell className="text-right">{t('Total')}</TableHeaderCell>
                                        <TableHeaderCell>{t('Created')}</TableHeaderCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {recentMessages.map((message) => (
                                        <TableRow key={`${message.url}-${message.management_code}`}>
                                            <TableCell>{message.channel}</TableCell>
                                            <TableCell>{message.message_type}</TableCell>
                                            <TableCell>
                                                <Link href={message.url} className="text-orbit-primary hover:underline">
                                                    {message.management_code}
                                                </Link>
                                            </TableCell>
                                            <TableCell>
                                                <StatusBadge value={message.status} />
                                            </TableCell>
                                            <TableCell className="text-right">{message.total_count}</TableCell>
                                            <TableCell>{message.created_at}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title={t('Message type mix')} />
                    <CardBody>
                        {chartData.length === 0 ? (
                            <EmptyState
                                icon="bs.pie-chart"
                                heading={apiEmptyHeading ?? t('No chart data.')}
                            />
                        ) : (
                            <>
                                <ResponsiveContainer width="100%" height={240}>
                                    <PieChart>
                                        <Tooltip formatter={(value: number, _name, item) => [`${value}`, item.payload.name]} />
                                        <Pie
                                            data={chartData}
                                            dataKey="value"
                                            nameKey="name"
                                            innerRadius={56}
                                            outerRadius={92}
                                            paddingAngle={2}
                                        >
                                            {chartData.map((entry, index) => (
                                                <Cell key={entry.name} fill={CHART_COLORS[index % CHART_COLORS.length]} />
                                            ))}
                                        </Pie>
                                    </PieChart>
                                </ResponsiveContainer>
                                <div className="mt-4 space-y-2">
                                    {chartData.map((entry, index) => (
                                        <div key={entry.name} className="flex items-center justify-between text-sm">
                                            <span className="flex items-center gap-2">
                                                <span
                                                    className="h-2.5 w-2.5 rounded-full"
                                                    style={{ backgroundColor: CHART_COLORS[index % CHART_COLORS.length] }}
                                                />
                                                {entry.name}
                                            </span>
                                            <span className="font-medium text-gray-700 dark:text-gray-200">
                                                {entry.percent}%
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </>
                        )}
                    </CardBody>
                </Card>
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader title={t('Registered senders')}>
                        {hub.links?.senders ? (
                            <Link href={hub.links.senders} className="text-sm text-orbit-primary hover:underline">
                                {t('View all')}
                            </Link>
                        ) : null}
                    </CardHeader>
                    <CardBody>
                        {senders.length === 0 ? (
                            <EmptyState
                                icon="bs.telephone"
                                heading={apiEmptyHeading ?? t('No senders found.')}
                            />
                        ) : (
                            <div className="space-y-3">
                                {senders.map((sender) => (
                                    <div
                                        key={`${sender.alias}-${sender.number}`}
                                        className="flex items-start justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900 dark:text-gray-100">{sender.alias}</p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{sender.number}</p>
                                        </div>
                                        <div className="text-right">
                                            <StatusBadge value={sender.status} />
                                            <p className="mt-1 text-xs text-gray-400">{sender.type}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title={t('Kakao profiles')}>
                        {hub.links?.profiles ? (
                            <Link href={hub.links.profiles} className="text-sm text-orbit-primary hover:underline">
                                {t('View all')}
                            </Link>
                        ) : null}
                    </CardHeader>
                    <CardBody>
                        {profiles.length === 0 ? (
                            <EmptyState
                                icon="bs.person-badge"
                                heading={apiEmptyHeading ?? t('No Kakao profiles found.')}
                            />
                        ) : (
                            <div className="space-y-3">
                                {profiles.map((profile) => (
                                    <div
                                        key={`${profile.name}-${profile.yellow_id}`}
                                        className="flex items-start justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900 dark:text-gray-100">{profile.name}</p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{profile.yellow_id}</p>
                                            <p className="mt-1 text-xs text-gray-400">{profile.sender_key}</p>
                                        </div>
                                        <StatusBadge value={profile.status} />
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardBody>
                </Card>
            </div>

            <Card>
                <CardHeader title={t('Synced templates')}>
                    {hub.links?.templates ? (
                        <Link href={hub.links.templates} className="text-sm text-orbit-primary hover:underline">
                            {t('Manage templates')}
                        </Link>
                    ) : null}
                </CardHeader>
                <CardBody>
                    {templates.length === 0 ? (
                        <EmptyState icon="bs.file-earmark-richtext" heading={t('No synced templates yet.')} />
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            {templates.map((template) => (
                                <Link
                                    key={template.template_code}
                                    href={template.url}
                                    className="rounded-xl border border-gray-200 p-4 transition hover:border-orbit-primary/40 hover:shadow-sm dark:border-gray-800"
                                >
                                    <p className="truncate font-medium text-gray-900 dark:text-gray-100">
                                        {template.template_name}
                                    </p>
                                    <p className="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                        {template.template_code}
                                    </p>
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        <StatusBadge value={template.inspection_status} />
                                        <StatusBadge value={template.status} />
                                    </div>
                                    <p className="mt-3 text-xs text-gray-400">{template.synced_at}</p>
                                </Link>
                            ))}
                        </div>
                    )}
                </CardBody>
            </Card>
        </div>
    );
}
