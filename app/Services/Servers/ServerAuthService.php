<?php

namespace Convoy\Services\Servers;

use Convoy\Models\Server;
use Convoy\Repositories\Proxmox\Server\ProxmoxConfigRepository;
use Convoy\Repositories\Proxmox\Server\ProxmoxGuestAgentRepository;

class ServerAuthService
{
    public function __construct(private ProxmoxConfigRepository $configRepository, private ProxmoxGuestAgentRepository $guestAgentRepository)
    {
    }

    public function updatePassword(Server $server, string $password)
    {
        try {
            $OsInfo = $this->guestAgentRepository->setServer($server)->guestAgentOs();
            if (str_contains($OsInfo["result"]["name"], "Windows")) {
                $username = "Administrator";
            } else {
                $username = "root";
            }
            $this->guestAgentRepository->setServer($server)->updateGuestAgentPassword($username, $password);
            $this->configRepository->setServer($server)->update(['cipassword' => $password]);
        } catch (\Exception $e) {
            $this->configRepository->setServer($server)->update(['cipassword' => $password]);
        }
    }

    public function updateWindowsPassword(Server $server, string $password) {
        $this->guestAgentRepository->setServer($server)->updateGuestAgentPassword("Administrator", $password);
    }

    public function getSSHKeys(Server $server)
    {
        $raw = collect($this->configRepository->setServer($server)->getConfig())->where('key', '=', 'sshkeys')->first()['value'] ?? '';

        return rawurldecode($raw);
    }

    public function updateSSHKeys(Server $server, ?string $keys)
    {
        if (!empty($keys)) {
            $this->configRepository->setServer($server)->update(['sshkeys' => rawurlencode($keys)]);
        } else {
            $this->configRepository->setServer($server)->update(['delete' => 'sshkeys']);
        }
    }
}