<?php

namespace Database\Seeders;

use App\Models\HazardQuestion;
use Illuminate\Database\Seeder;

class HazardQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'scene_id' => 'pump-piping',
                'title' => 'Pump & Piping Area Walkdown',
                'image' => '/assets/scenes-optimized/pump-piping.jpg',
                'source' => 'OSHA PSM: mechanical integrity and operating procedures',
                'question' => 'Which action is most appropriate when a flange leak and oil spill are seen near operating equipment?',
                'options' => [
                    'Stop, control the area, report the abnormal condition, and initiate maintenance response.',
                    'Continue the walkdown because small leaks are normal in operating areas.',
                    'Clean the floor only, then return the equipment to normal service.',
                    'Ask the next shift to monitor it if the leak becomes larger.',
                ],
                'answer' => 0,
                'explanation' => 'Loss of containment is a process-safety warning sign. The correct response is to control the area, report it, and restore mechanical integrity rather than treating it as housekeeping only.',
            ],
            [
                'scene_id' => 'relief-device',
                'title' => 'Relief Device Line-Up',
                'image' => '/assets/scenes-optimized/relief-device.jpg',
                'source' => 'OSHA PSM: process safety information and mechanical integrity',
                'question' => 'What is the main process-safety concern if a PSV block valve is closed or a relief bypass is left open without control?',
                'options' => [
                    'The equipment may lose its designed overpressure protection barrier.',
                    'The unit will automatically produce less product.',
                    'The issue is only administrative if the valve is physically reachable.',
                    'The risk is limited to personal injury and not process safety.',
                ],
                'answer' => 0,
                'explanation' => 'Relief devices are critical protection barriers. Incorrect line-up or uncontrolled bypass can remove the intended protection against overpressure.',
            ],
            [
                'scene_id' => 'hot-work',
                'title' => 'Hot Work Near Process Equipment',
                'image' => '/assets/scenes-optimized/hot-work.jpg',
                'source' => 'OSHA PSM: hot work permit requirements',
                'question' => 'Before hot work near process equipment, which control is most important?',
                'options' => [
                    'Verify the hot work permit, gas testing/monitoring, ignition controls, fire watch, and emergency equipment access.',
                    'Start quickly so the work finishes before conditions change.',
                    'Rely only on the worker PPE because the activity is short.',
                    'Move the extinguisher away so it does not block the job area.',
                ],
                'answer' => 0,
                'explanation' => 'Hot work introduces ignition risk. Permit control, gas testing, spark control, fire watch, and clear firefighting access are core safeguards.',
            ],
            [
                'scene_id' => 'startup-maintenance',
                'title' => 'Startup After Maintenance',
                'image' => '/assets/scenes-optimized/startup-maintenance.jpg',
                'source' => 'OSHA PSM: pre-startup safety review',
                'question' => 'What should happen before restarting equipment after maintenance?',
                'options' => [
                    'Complete pre-startup safety review: verify construction/maintenance completion, procedure readiness, training, and safety systems.',
                    'Restart first, then correct any remaining checklist gaps during operation.',
                    'Remove only the visible tools and assume the isolation status is correct.',
                    'Let the operator decide based on experience if the startup feels routine.',
                ],
                'answer' => 0,
                'explanation' => 'A pre-startup safety review confirms the system is ready before introducing process hazards back into equipment.',
            ],
            [
                'scene_id' => 'moc-change',
                'title' => 'Process Line Change / MOC Scenario',
                'image' => '/assets/scenes-optimized/moc-change.jpg',
                'source' => 'OSHA PSM: management of change',
                'question' => 'A temporary jumper hose and bypass have been installed around a control valve. What is the correct process-safety action?',
                'options' => [
                    'Raise and approve Management of Change, assess hazards, communicate the change, and define reinstatement controls.',
                    'Accept it as temporary because temporary changes do not require MOC.',
                    'Document it only after the job is complete.',
                    'Use a handwritten field label as the only control.',
                ],
                'answer' => 0,
                'explanation' => 'Temporary changes can create major accident risk. MOC is required to assess hazards, authorize controls, communicate changes, and manage reinstatement.',
            ],
            [
                'scene_id' => 'chemical-transfer',
                'title' => 'Chemical Transfer / Loading Bay',
                'image' => '/assets/scenes-optimized/chemical-transfer.jpg',
                'source' => 'PSM: operating discipline and chemical handling controls',
                'question' => 'During chemical transfer, which finding should stop the job until controls are restored?',
                'options' => [
                    'A damaged transfer hose, missing bonding/earthing, or unavailable spill control.',
                    'A clean label on the drum and a clear transfer area.',
                    'A completed checklist with correct PPE and hose inspection.',
                    'A standby person confirming the transfer route.',
                ],
                'answer' => 0,
                'explanation' => 'Chemical transfer needs compatible equipment, static control, spill response, PPE, and clear labelling before flow starts.',
            ],
            [
                'scene_id' => 'tank-farm',
                'title' => 'Tank Farm Bund Area',
                'image' => '/assets/scenes-optimized/tank-farm.jpg',
                'source' => 'PSM: containment and emergency response barriers',
                'question' => 'Why is an open bund drain during tank operation a serious process-safety concern?',
                'options' => [
                    'It can defeat secondary containment during a leak or spill.',
                    'It only affects housekeeping appearance.',
                    'It improves drainage and should normally stay open.',
                    'It matters only during heavy rain.',
                ],
                'answer' => 0,
                'explanation' => 'Bunds are secondary containment barriers. Drains must be controlled so released material does not escape the containment area.',
            ],
            [
                'scene_id' => 'control-panel',
                'title' => 'Control Panel / Alarm Response',
                'image' => '/assets/scenes-optimized/control-panel.jpg',
                'source' => 'PSM: safe operating limits and alarm response',
                'question' => 'What should an operator do when multiple alarms and an abnormal pressure trend appear?',
                'options' => [
                    'Prioritize, investigate, act within safe operating limits, and escalate if needed.',
                    'Acknowledge all alarms first and wait for the trend to normalize.',
                    'Silence the alarm because frequent alarms are normal.',
                    'Leave the response for the next shift if production is stable.',
                ],
                'answer' => 0,
                'explanation' => 'Alarm response is a process-safety barrier. Acknowledging alarms without action can hide a developing abnormal situation.',
            ],
            [
                'scene_id' => 'fire-gas',
                'title' => 'Fire & Gas / Emergency Equipment Area',
                'image' => '/assets/scenes-optimized/fire-gas.jpg',
                'source' => 'PSM: safety critical equipment availability',
                'question' => 'Which condition most directly weakens fire and gas protection?',
                'options' => [
                    'Covered detectors, blocked call points, or inaccessible emergency equipment.',
                    'A clearly marked escape route and tested emergency lighting.',
                    'A visible inspection tag on a maintained extinguisher.',
                    'A clean area around the emergency shower.',
                ],
                'answer' => 0,
                'explanation' => 'Fire and gas equipment must be available, visible, and unobstructed so detection and response barriers work when needed.',
            ],
            [
                'scene_id' => 'confined-space',
                'title' => 'Confined Space / Vessel Entry Preparation',
                'image' => '/assets/scenes-optimized/confined-space.jpg',
                'source' => 'PSM: isolation, gas testing, and entry readiness',
                'question' => 'Before confined space entry, which set of controls must be verified?',
                'options' => [
                    'Isolation, gas testing, ventilation, standby person, permit, and rescue readiness.',
                    'Only PPE, because the entry duration is short.',
                    'Only a verbal instruction from the supervisor.',
                    'Only barricades around the manway.',
                ],
                'answer' => 0,
                'explanation' => 'Confined space entry requires multiple verified barriers before entry, not just a single control.',
            ],
            [
                'scene_id' => 'relief-device-bypass',
                'title' => 'Relief Device Bypass Control',
                'image' => '/assets/scenes-optimized/relief-device.jpg',
                'source' => 'PSM: bypass control and relief protection',
                'question' => 'A safety-critical bypass is needed temporarily. What is the correct control?',
                'options' => [
                    'Formal approval, risk assessment, compensating controls, tracking, and timely reinstatement.',
                    'A sticky note on the panel is enough if everyone sees it.',
                    'Leave it to operator memory because it is temporary.',
                    'Bypasses do not need control if production is stable.',
                ],
                'answer' => 0,
                'explanation' => 'Temporary bypasses can remove protection layers. They need formal approval and active tracking until reinstated.',
            ],
            [
                'scene_id' => 'hot-work-drain',
                'title' => 'Hot Work Ignition Control',
                'image' => '/assets/scenes-optimized/hot-work.jpg',
                'source' => 'PSM: hot work and ignition-source control',
                'question' => 'Why should open drains and oily rags be controlled before hot work?',
                'options' => [
                    'They can provide fuel or vapour paths that increase ignition risk.',
                    'They only make the area look untidy.',
                    'They help sparks cool faster.',
                    'They matter only after the job is complete.',
                ],
                'answer' => 0,
                'explanation' => 'Hot work controls must consider vapour paths, combustibles, gas testing, fire watch, and emergency response readiness.',
            ],
            [
                'scene_id' => 'startup-blinds',
                'title' => 'Startup Blind and Isolation Check',
                'image' => '/assets/scenes-optimized/startup-maintenance.jpg',
                'source' => 'PSM: pre-startup safety review',
                'question' => 'What is the risk if blinds, tools, or temporary bypasses remain after maintenance?',
                'options' => [
                    'The plant may restart with an incorrect line-up or impaired protection.',
                    'Startup becomes faster because fewer checks are needed.',
                    'The items can be removed after normal operation begins.',
                    'There is no process-safety concern if the operator is experienced.',
                ],
                'answer' => 0,
                'explanation' => 'Pre-startup checks confirm mechanical completion, correct line-up, and restored safeguards before process hazards are reintroduced.',
            ],
            [
                'scene_id' => 'pump-tags',
                'title' => 'Line Identification and Field Verification',
                'image' => '/assets/scenes-optimized/pump-piping.jpg',
                'source' => 'PSM: process safety information and operating discipline',
                'question' => 'Why are missing line tags and unreadable gauges unsafe during field work?',
                'options' => [
                    'They make it harder to verify the correct equipment, condition, and operating limit.',
                    'They only affect the maintenance filing system.',
                    'They are acceptable if the area is familiar.',
                    'They matter only during audits.',
                ],
                'answer' => 0,
                'explanation' => 'Clear identification and readable instruments support correct decisions during operations, isolation, maintenance, and emergency response.',
            ],
            [
                'scene_id' => 'moc-communication',
                'title' => 'MOC Communication',
                'image' => '/assets/scenes-optimized/moc-change.jpg',
                'source' => 'PSM: management of change communication',
                'question' => 'After an approved process change, what must happen before affected personnel operate the system?',
                'options' => [
                    'Communicate the change, update documents/procedures, and confirm people understand the new controls.',
                    'Wait until the next annual refresher training.',
                    'Rely on field labels only.',
                    'Keep the change informal if it is temporary.',
                ],
                'answer' => 0,
                'explanation' => 'MOC is not complete until affected people understand the change, hazards, and required controls.',
            ],
        ];

        foreach ($questions as $q) {
            HazardQuestion::create($q);
        }
    }
}
