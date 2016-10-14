<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Mailer\Provider;

use Sylius\Component\Mailer\Model\EmailInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Jérémy Leherpeur <jeremy@leherpeur.net>
 */
final class EmailProvider implements EmailProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $emailFactory;

    /**
     * @var RepositoryInterface
     */
    protected $emailRepository;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param FactoryInterface $emailFactory
     * @param RepositoryInterface $emailRepository
     * @param array $configuration
     */
    public function __construct(
        FactoryInterface $emailFactory,
        RepositoryInterface $emailRepository,
        array $configuration
    ) {
        $this->emailFactory = $emailFactory;
        $this->emailRepository = $emailRepository;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail($code)
    {
        $email = $this->emailRepository->findOneBy(['code' => $code]);

        if (null === $email) {
            $email = $this->getEmailFromConfiguration($code);
        }

        return $email;
    }

    /**
     * @param string $code
     *
     * @return EmailInterface
     */
    private function getEmailFromConfiguration($code)
    {
        Assert::keyExists($this->configuration, $code, sprintf('Email with code "%s" does not exist!', $code));

        /** @var EmailInterface $email */
        $email = $this->emailFactory->createNew();
        $configuration = $this->configuration[$code];

        $email->setCode($code);
        $email->setSubject($configuration['subject']);
        $email->setTemplate($configuration['template']);

        if (isset($configuration['enabled']) && false === $configuration['enabled']) {
            $email->setEnabled(false);
        }
        if (isset($configuration['sender']['name'])) {
            $email->setSenderName($configuration['sender']['name']);
        }
        if (isset($configuration['sender']['address'])) {
            $email->setSenderAddress($configuration['sender']['address']);
        }

        return $email;
    }
}
