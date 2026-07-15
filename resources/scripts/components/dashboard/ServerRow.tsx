import React, { memo, useEffect, useRef, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faEthernet,
    faHdd,
    faMemory,
    faMicrochip,
    faPlay,
    faRedo,
    faServer,
    faStop,
} from '@fortawesome/free-solid-svg-icons';
import { Link } from 'react-router-dom';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';
import sendPowerSignal, { PowerSignal } from '@/api/server/sendPowerSignal';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import tw from 'twin.macro';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Spinner from '@/components/elements/Spinner';
import styled from 'styled-components/macro';
import isEqual from 'react-fast-compare';

// Determines if the current value is in an alarm threshold so we can show it in red rather
// than the more faded default style.
const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

const Icon = memo(
    styled(FontAwesomeIcon)<{ $alarm: boolean }>`
        ${(props) => (props.$alarm ? tw`text-red-400` : tw`text-neutral-500`)};
    `,
    isEqual
);

const IconDescription = styled.p<{ $alarm: boolean }>`
    ${tw`text-sm ml-2`};
    ${(props) => (props.$alarm ? tw`text-white` : tw`text-neutral-400`)};
`;

const UsageBar = styled.div<{ $alarm: boolean }>`
    ${tw`h-1 rounded-full mt-1.5 overflow-hidden`};
    background: var(--color-border, #2a2a3a);

    & > div {
        ${tw`h-full rounded-full transition-all duration-500`};
        background: ${(props) =>
            props.$alarm
                ? 'linear-gradient(90deg, #ff4757, #ff6b81)'
                : 'var(--gradient-accent, linear-gradient(90deg, #7382FF, #a78bfa))'};
    }
`;

const PowerButton = styled.button<{ $color: 'green' | 'red' | 'accent' }>`
    ${tw`w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-150`};
    background: var(--color-surface, #1e1e2a);
    border: 1px solid var(--color-border, #2a2a3a);
    color: var(--text-secondary, #b0b0c0);
    cursor: pointer;

    &:hover:not(:disabled) {
        color: #fff;
        transform: translateY(-1px);
        ${(props) =>
            props.$color === 'green'
                ? `background: #2ed573; border-color: #2ed573; box-shadow: 0 0 12px rgba(46, 213, 115, 0.4);`
                : props.$color === 'red'
                ? `background: #ff4757; border-color: #ff4757; box-shadow: 0 0 12px rgba(255, 71, 87, 0.4);`
                : `background: var(--color-accent, #7382FF); border-color: var(--color-accent, #7382FF); box-shadow: 0 0 12px var(--color-accent-glow, rgba(115, 130, 255, 0.4));`};
    }

    &:disabled {
        opacity: 0.35;
        cursor: default;
    }
`;

const StatusIndicatorBox = styled(GreyRowBox)<{ $status: ServerPowerState | undefined }>`
    ${tw`grid grid-cols-12 gap-4 relative`};

    & .status-bar {
        ${tw`w-1.5 absolute right-0 z-20 rounded-full m-1 transition-all duration-300`};
        height: calc(100% - 0.5rem);
        opacity: 0.7;

        ${({ $status }) =>
            !$status || $status === 'offline'
                ? tw`bg-red-500`
                : $status === 'running'
                ? tw`bg-green-500`
                : tw`bg-yellow-500`};

        ${({ $status }) =>
            $status === 'running'
                ? `animation: vh-pulse-glow 2.5s ease-in-out infinite;`
                : $status === 'offline'
                ? `box-shadow: 0 0 8px rgba(255, 71, 87, 0.3);`
                : `box-shadow: 0 0 8px rgba(255, 165, 2, 0.3);`};
    }

    &:hover .status-bar {
        opacity: 1;
    }

    & .power-actions {
        ${tw`transition-opacity duration-200`};
        opacity: 0;
    }

    &:hover .power-actions {
        opacity: 1;
    }

    @media (hover: none) {
        & .power-actions {
            opacity: 1;
        }
    }
`;

type Timer = ReturnType<typeof setInterval>;

export default ({ server, className }: { server: Server; className?: string }) => {
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [isSuspended, setIsSuspended] = useState(server.status === 'suspended');
    const [stats, setStats] = useState<ServerStats | null>(null);
    const [powerLock, setPowerLock] = useState(false);

    const getStats = () =>
        getServerResourceUsage(server.uuid)
            .then((data) => setStats(data))
            .catch((error) => console.error(error));

    useEffect(() => {
        setIsSuspended(stats?.isSuspended || server.status === 'suspended');
    }, [stats?.isSuspended, server.status]);

    useEffect(() => {
        // Don't waste a HTTP request if there is nothing important to show to the user because
        // the server is suspended.
        if (isSuspended || server.isNodeUnderMaintenance) return;

        getStats().then(() => {
            interval.current = setInterval(() => getStats(), 30000);
        });

        return () => {
            interval.current && clearInterval(interval.current);
        };
    }, [isSuspended, server.isNodeUnderMaintenance]);

    const onPowerAction = (signal: PowerSignal, e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (powerLock) return;
        if (signal !== 'start' && !window.confirm(`Are you sure you want to ${signal} "${server.name}"?`)) {
            return;
        }

        setPowerLock(true);
        sendPowerSignal(server.uuid, signal)
            .then(() => {
                // Optimistically bump the visible status, then re-poll shortly after.
                setStats((prev) => (prev ? { ...prev, status: signal === 'start' ? 'starting' : 'stopping' } : prev));
                setTimeout(() => getStats(), 3000);
            })
            .catch((error) => console.error(error))
            .then(() => setTimeout(() => setPowerLock(false), 2000));
    };

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }

    const diskLimit = server.limits.disk !== 0 ? bytesToString(mbToBytes(server.limits.disk)) : 'Unlimited';
    const memoryLimit = server.limits.memory !== 0 ? bytesToString(mbToBytes(server.limits.memory)) : 'Unlimited';
    const cpuLimit = server.limits.cpu !== 0 ? server.limits.cpu + ' %' : 'Unlimited';

    // Percentages for the usage bars, clamped to 0-100. Unlimited resources scale
    // against sensible visual baselines instead of showing an empty bar.
    const cpuPercent = stats ? Math.min(100, (stats.cpuUsagePercent / (server.limits.cpu || 100)) * 100) : 0;
    const memoryPercent = stats
        ? server.limits.memory > 0
            ? Math.min(100, (stats.memoryUsageInBytes / mbToBytes(server.limits.memory)) * 100)
            : 0
        : 0;
    const diskPercent = stats
        ? server.limits.disk > 0
            ? Math.min(100, (stats.diskUsageInBytes / mbToBytes(server.limits.disk)) * 100)
            : 0
        : 0;

    const status = stats?.status;
    const showPower = !!stats && !isSuspended && !server.isNodeUnderMaintenance && !server.isTransferring;

    return (
        <StatusIndicatorBox as={Link} to={`/server/${server.id}`} className={className} $status={stats?.status}>
            <div css={tw`flex items-center col-span-12 sm:col-span-5 lg:col-span-4`}>
                <div className={'icon mr-4'}>
                    <FontAwesomeIcon icon={faServer} />
                </div>
                <div css={tw`min-w-0`}>
                    <p css={tw`text-lg break-words`}>{server.name}</p>
                    {!!server.description && (
                        <p css={tw`text-sm text-neutral-300 break-words line-clamp-2`}>{server.description}</p>
                    )}
                </div>
            </div>
            <div css={tw`flex-1 ml-4 lg:block lg:col-span-2 hidden self-center`}>
                <div css={tw`flex justify-center`}>
                    <FontAwesomeIcon icon={faEthernet} css={tw`text-neutral-500`} />
                    <p css={tw`text-sm text-neutral-400 ml-2`}>
                        {server.allocations
                            .filter((alloc) => alloc.isDefault)
                            .map((allocation) => (
                                <React.Fragment key={allocation.ip + allocation.port.toString()}>
                                    {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                </React.Fragment>
                            ))}
                    </p>
                </div>
            </div>
            <div css={tw`hidden col-span-7 lg:col-span-4 sm:flex items-baseline justify-center`}>
                {!stats || isSuspended || server.isNodeUnderMaintenance ? (
                    isSuspended ? (
                        <div css={tw`flex-1 text-center`}>
                            <span css={tw`bg-red-500 rounded px-2 py-1 text-red-100 text-xs`}>
                                {server.status === 'suspended' ? 'Suspended' : 'Connection Error'}
                            </span>
                        </div>
                    ) : server.isNodeUnderMaintenance ? (
                        <div css={tw`flex-1 text-center`}>
                            <span css={tw`bg-yellow-500 rounded px-2 py-1 text-yellow-100 text-xs`}>
                                Under Maintenance
                            </span>
                        </div>
                    ) : server.isTransferring || server.status ? (
                        <div css={tw`flex-1 text-center`}>
                            <span css={tw`bg-neutral-500 rounded px-2 py-1 text-neutral-100 text-xs`}>
                                {server.isTransferring
                                    ? 'Transferring'
                                    : server.status === 'installing'
                                    ? 'Installing'
                                    : server.status === 'restoring_backup'
                                    ? 'Restoring Backup'
                                    : 'Unavailable'}
                            </span>
                        </div>
                    ) : (
                        <Spinner size={'small'} />
                    )
                ) : (
                    <React.Fragment>
                        <div css={tw`flex-1 ml-4 sm:block hidden`}>
                            <div css={tw`flex justify-center`}>
                                <Icon icon={faMicrochip} $alarm={alarms.cpu} />
                                <IconDescription $alarm={alarms.cpu}>
                                    {stats.cpuUsagePercent.toFixed(2)} %
                                </IconDescription>
                            </div>
                            <UsageBar $alarm={alarms.cpu}>
                                <div style={{ width: `${cpuPercent}%` }} />
                            </UsageBar>
                            <p css={tw`text-xs text-neutral-600 text-center mt-1`}>of {cpuLimit}</p>
                        </div>
                        <div css={tw`flex-1 ml-4 sm:block hidden`}>
                            <div css={tw`flex justify-center`}>
                                <Icon icon={faMemory} $alarm={alarms.memory} />
                                <IconDescription $alarm={alarms.memory}>
                                    {bytesToString(stats.memoryUsageInBytes)}
                                </IconDescription>
                            </div>
                            <UsageBar $alarm={alarms.memory}>
                                <div style={{ width: `${memoryPercent}%` }} />
                            </UsageBar>
                            <p css={tw`text-xs text-neutral-600 text-center mt-1`}>of {memoryLimit}</p>
                        </div>
                        <div css={tw`flex-1 ml-4 sm:block hidden`}>
                            <div css={tw`flex justify-center`}>
                                <Icon icon={faHdd} $alarm={alarms.disk} />
                                <IconDescription $alarm={alarms.disk}>
                                    {bytesToString(stats.diskUsageInBytes)}
                                </IconDescription>
                            </div>
                            <UsageBar $alarm={alarms.disk}>
                                <div style={{ width: `${diskPercent}%` }} />
                            </UsageBar>
                            <p css={tw`text-xs text-neutral-600 text-center mt-1`}>of {diskLimit}</p>
                        </div>
                    </React.Fragment>
                )}
            </div>
            <div css={tw`hidden lg:flex col-span-2 items-center justify-end pr-4 space-x-2`}>
                {showPower && (
                    <div className={'power-actions'} css={tw`flex items-center space-x-2`}>
                        <PowerButton
                            type={'button'}
                            title={'Start server'}
                            $color={'green'}
                            disabled={powerLock || status !== 'offline'}
                            onClick={(e) => onPowerAction('start', e)}
                        >
                            <FontAwesomeIcon icon={faPlay} size={'xs'} />
                        </PowerButton>
                        <PowerButton
                            type={'button'}
                            title={'Restart server'}
                            $color={'accent'}
                            disabled={powerLock || !status || status === 'offline'}
                            onClick={(e) => onPowerAction('restart', e)}
                        >
                            <FontAwesomeIcon icon={faRedo} size={'xs'} />
                        </PowerButton>
                        <PowerButton
                            type={'button'}
                            title={'Stop server'}
                            $color={'red'}
                            disabled={powerLock || !status || status === 'offline'}
                            onClick={(e) => onPowerAction('stop', e)}
                        >
                            <FontAwesomeIcon icon={faStop} size={'xs'} />
                        </PowerButton>
                    </div>
                )}
            </div>
            <div className={'status-bar'} />
        </StatusIndicatorBox>
    );
};
