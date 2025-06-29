# Boss-Secretary Module for FreePBX
A module for FreePBX 14+ that implements an intelligent "Boss-Secretary" call flow. Calls intended for a "Boss" are first intercepted and directed to a "Secretary," who acts as a gatekeeper, with the flexibility of a whitelist for direct access.

This project was developed following best practices for module creation in FreePBX, using the BMO (Big Module Object) architecture and being as non-invasive as possible.

## Key Features
Intelligent Routing: Forwards calls intended for a "Boss" extension to a designated "Secretary" extension.

Whitelist for Direct Access: Configure a list of numbers (internal or external) that can bypass the secretary and call the boss directly.

Multi-Pair Management: The interface allows for the configuration of multiple Boss-Secretary rules, each with its own whitelist.

Integrated Graphical Interface: All settings are managed through a new page in the "Applications" menu of FreePBX, without the need to manually edit configuration files.

User-Friendly Extension Selection: Uses the Select2 library to facilitate searching and selecting the boss's and secretary's extensions, minimizing errors.

Feedback Notifications: Displays success or error messages ("toast notifications") after each action, improving the user experience.

Secure Dialplan Integration: The module safely injects its logic into the Asterisk Dialplan without overwriting existing contexts, ensuring compatibility and stability.

## How It Works
The module uses a Dialplan Hook to integrate with FreePBX. Instead of creating contexts that could conflict with the standard logic, it uses the $ext->splice() function to inject a Goto command at the beginning of the ext-local context for the boss's extension.

This diverts the call to a custom subroutine that:

Checks the caller's CallerID against the whitelist.

If the number is allowed, the call is returned to the standard FreePBX flow (ext-local), at a priority that prevents loops, to be completed normally.

If the number is not allowed, the call is directed to the standard flow of the secretary's extension.

This approach ensures that the module only manages the initial diversion, leaving all other call processing (including Follow Me, voicemail, etc.) to FreePBX itself.

## Prerequisites
FreePBX 14 or higher.

PHP 5.6 or higher.

## Installation
Download the latest version of the module from https://github.com/mrpbueno/bosssec/releases

Navigate to Admin > Module Admin.

Click on Upload Modules and upload the module's zip file.

Return to the module list and install the Boss-Secretary module.

## Usage and Configuration
After installation, the module will be available in the FreePBX menu.

Navigate to Applications > Boss-Secretary.

You will see a list of all existing rules. To add a new one, click on "Add".

Fill out the form:

Boss's Name: A descriptive name for the rule (e.g., "Financial Director").

Boss's Extension: Select the boss's extension from the list.

Secretary's Extension: Select the secretary's extension where calls will be forwarded.

Whitelist Numbers: Add the numbers that will have direct access. You can separate them by space, comma, or line break.

Rule Enabled: Set whether the rule is active or not.

Click "Submit" to save.

Important: After saving any changes, the red "Apply Config" button will appear at the top of the page. Click it so that your new dialing rules are applied in Asterisk.

## License
This project is licensed under the GPLv3 license.
