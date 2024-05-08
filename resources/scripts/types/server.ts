import {PaginatedResult} from "@/utils/http.ts";

export type ServerLifecycleStatus =
    | 'installing'
    | 'install_failed'
    | 'suspended'
    | 'restoring_backup'
    | 'restoring_snapshot'
    | 'deleting'
    | 'deletion_failed'
    | null

export type ServerPowerState = 'running' | 'stopped' | 'suspended'

export interface Server {
    id: number
    uuid: string
    uuidShort: string
    userId: number
    nodeId: number
    vmid: number
    hostname: string
    name: string
    description: string | null
    status: ServerLifecycleStatus
    cpu: number
    memory: number
    disk: number
    bandwidthUsage: number
    bandwidthLimit: number | null
}

export type PaginatedServers = PaginatedResult<Server>
