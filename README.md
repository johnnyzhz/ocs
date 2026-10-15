# Open Conference Systems (OCS)

**Fork version:** 3.0.0 (ISDSA-maintained)  
**Based on:** PKP OCS 2.3.6  
**Upstream tag:** `ocs-2_3_6-0`  
**Upstream release date:** May 28, 2014

## About this fork

Open Conference Systems (OCS) was developed by the Public Knowledge Project (PKP). The upstream OCS 2.3.6 project is no longer maintained. This ISDSA-maintained fork includes local compatibility changes and a mobile-friendly theme; it does not change the upstream version recorded in the application metadata.

For information about PKP and its open research systems, visit <http://pkp.sfu.ca/>. A working example of this fork is available at <https://meeting.isdsa.org>.

## Compatibility and requirements

### Compatibility targets for this fork

- PHP 8.x
- MySQL 8.x

These are targets, not a guarantee that every version combination or application workflow has been verified. The full PHP source tree has been syntax-checked with PHP 8.2.30, and targeted runtime smoke tests have been run. The current deployment uses MySQL 8.4.7. Test your exact versions and workflows before production use.

OCS 2.3.6 predates PHP 8 and MySQL 8, so its original system requirements do not establish compatibility with these versions.

### Upstream-era platform notes

The original documentation listed Apache >= 1.3.2x or >= 2.0.4x, or Microsoft IIS 6, and operating systems supported by those servers (including Linux, BSD, Solaris, Mac OS X, and Windows). These are historical upstream requirements, not a recommendation to deploy end-of-life server software. Choose currently supported server software and verify it with this fork.

PKP could not test every software and platform combination; compatibility or support for untested combinations was not implied.

## Installation

Before installing, review this document, `docs/RELEASE`, and the FAQ in this directory.

1. Extract the OCS archive into the desired location in your web documents directory.
2. Make the following files and directories, including their contents, writable by the web server as needed. Adjust ownership or permissions with tools such as `chown` or `chmod`:
   - `config.inc.php` (optional; if it is not writable, you will be prompted to overwrite it manually during installation)
   - `public/`
   - `cache/`
   - `cache/t_cache/`
   - `cache/t_config/`
   - `cache/t_compile/`
   - `cache/_db/`
3. Create a writable directory for uploaded files, such as submission files. Place it outside the web-accessible directory if possible; otherwise, protect it from direct web access (for example, with server access rules).
4. Install using either method:
   - **Web installer:** Open `https://yourdomain.com/path/to/ocs/` and follow the on-screen instructions. Use HTTPS for a production deployment.
   - **Command line:** From the OCS directory, run `php tools/install.php`. If the CLI user differs from the web-server user, adjust ownership or permissions on the `public/` and uploaded-files directories afterward.
5. After installation, review `config.inc.php` for additional settings and consult the FAQ for technical and server-configuration guidance.

## Security and backups

For a more secure deployment:

- Use a dedicated database with unique credentials for OCS.
- Configure regular automated backups of the database, and take a manual backup before upgrades or maintenance.
- Configure `config.inc.php` to use SHA-1 hashing rather than MD5, as recommended by the original OCS documentation.
- Enable `force_ssl_login` in `config.inc.php` so authenticated users connect over HTTPS.
- Store the uploaded-files directory outside the OCS installation and prevent direct web access to it. Restrict filesystem permissions, and coordinate its backups with database backups.

## Upgrading

See `docs/UPGRADE` for instructions on upgrading from OCS 1.x and OCS 2.x releases. Back up the database and uploaded files before upgrading.

## Localization

To add a language, localize the relevant XML files and place them in directories named with an appropriate locale code (for example, `fr_FR`). The English source directories are:

- `locale/en_US/`
- `dbscripts/xml/data/locale/en_US/`
- `help/en_US/`
- `registry/locale/en_US/`
- `rt/en_US/`
- `plugins/[plugin category]/[plugin name]/locale/`, where applicable

The critical translations required for the system to function are in `locale/`, `dbscripts/xml/data/locale/`, and `registry/locale/`. Add each new locale to `registry/locales.xml`; it can then be installed through the site-administration interface.

The original documentation listed these minimum requirements for full UTF-8 support:

- MySQL >= 4.1.1 or PostgreSQL >= 7.1
- PHP >= 4.3.0 compiled with the `mbstring` library

These are historical upstream minimums, not current compatibility recommendations for this fork.

## Scheduled tasks

OCS can run scheduled work such as sending reminder notifications. To enable it:

1. Set `scheduled_tasks` to `On` in `config.inc.php`.
2. Configure your operating system to run `tools/runScheduledTasks.php` periodically using the PHP command-line interpreter, preferably as the web-server user. The original documentation recommends running it at least once per day.

For example, a cron entry that runs the script hourly is:

```cron
0 * * * * php /path/to/ocs/tools/runScheduledTasks.php
```

On Windows, configure the equivalent command in Task Scheduler, using the full path to `php` and `tools/runScheduledTasks.php`. Ensure the task runs under an account with the required permissions.

## Third-party libraries

See `lib/libraries.txt` for the list of third-party libraries used by OCS.

## License

OCS is licensed under the GNU General Public License, version 2. See `docs/COPYING` for the complete license terms. Modification and redistribution are permitted under that license.

## Support

For technical issues, the PKP support forum is the recommended contact method:

- Forum: <http://pkp.sfu.ca/support/forum/>
- Bug reports: <http://pkp.sfu.ca/bugzilla/>
- Email: <pkp.contact@gmail.com>
