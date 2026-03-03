################################################################################
#
#  This file is part of SplashSync Project.
#
#  Copyright (C) Splash Sync <www.splashsync.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

################################################################################
# Mailjet API Sandbox Image for Testing
#
# This image is based on splashsync/openapi-sandbox:3.0
# and includes custom Entity definitions, Controllers and configuration
# to fake the Mailjet V3 API
################################################################################

FROM splashsync/openapi-sandbox:3.0

################################################################################
# Copy Custom Entity Definitions
COPY Entity/ /app/src/Entity/

################################################################################
# Copy Custom Controllers
COPY Controller/ /app/src/Controller/

################################################################################
# Copy Custom Commands
COPY Command/ /app/src/Command/

################################################################################
# Copy Custom Serializer
COPY Serializer/ /app/src/Serializer/

################################################################################
# Copy Custom Configuration
COPY config.yaml /app/config/packages/config.yaml

################################################################################
# Regenerate autoloader to include new classes, then rebuild cache
RUN rm -rf /app/var/cache/*
RUN composer dump-autoload --no-dev --optimize
RUN php bin/console cache:clear --no-debug
RUN php bin/console doctrine:schema:update --force --no-interaction --no-debug
RUN php bin/console app:seed-data --no-interaction
