<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $projectArray = [
            'Create Design of worksuite',
            'Install Application',
            'Modify Application',
            'Server Installation',
            'Web Installation',
            'Project Management',
            'User Management',
            'School Management',
            'Restaurant Management',
            'Examination System Project',
            'Cinema Ticket Booking System',
            'Airline Reservation System',
            'Website Copier Project',
            'Chat Application',
            'Payment Billing System',
            'Identification System',
            'Document management System',
            'Live Meeting',
            'Android task monitoring',
            'Sentiment analysis for product rating',
            'Fingerprint-based ATM system',
            'Advanced employee management system',
            'Image encryption using the AES algorithm',
            'Fingerprint voting system',
            'Weather forecasting system',
            'Android local train ticketing system',
            'Railway tracking and arrival time prediction system',
            'Android Patient Tracker',
            'Opinion mining for social networking platforms',
            'Automated payroll system with GPS tracking and image capture',
            'Data leakage detection system',
            'Credit card fraud detection',
            'AI shopping system',
            'Camera motion sensor system',
            'Bug tracker',
            'e-Learning platform',
            'Smart health prediction system',
            'Software piracy protection system',
            'Face detector',
            'Voice Recognition',
            'Chatbots',
            'Marketplace for handmade goods',
            'Social media platform for artists',
            'Job board for remote job listings',
            'Recipe sharing and meal planning app',
            'Event management and ticketing platform',
            'E-learning platform for language courses',
            'Appointment scheduling system',
            'Interior design service',
            'Crowdfunding platform for social causes',
            'Survey and data collection tool',
            'Wardrobe styling and fashion advice',
            'Real estate listing and property management platform',
            'Legal document preparation service',
            'Fitness and wellness coaching',
            'Tutoring and homework help platform',
            'Digital asset management system',
            'Inventory and stock management system',
            'Financial planning and budgeting tool',
            'Bookkeeping and accounting software',
            'Therapy and mental health support',
            'Workout and fitness tracking app',
            'Personal shopping and styling service',
            'Fundraising and donation platform',
            'Event planning and coordination service',
            'Project management and team collaboration tool',
            'Travel planning and itinerary creation service',
            'Language translation and localization service',
            'Resume and cover letter writing service',
            'Graphic design and branding service',
            'Content creation and copywriting service',
            'Music production and recording service',
            'Video editing and animation service',
            'Web development and programming service',
            'Search engine optimization (SEO) service',
            'Digital marketing and advertising service',
            'Public relations (PR) and media outreach service',
            'Customer service and support platform',
            'Market research and data analysis service',
            'Lead generation and sales management service',
            'Reputation management and crisis communications service',
            'Community management and engagement service',
            'Affiliate marketing and influencer outreach service',
            'Social media management and advertising service',
            'Email marketing and newsletter service',
            'Mobile app development and design service',
            'Augmented reality (AR) and virtual reality (VR) development service',
            'Blockchain and cryptocurrency development service',
            'Artificial intelligence (AI) and machine learning (ML) development service',
            'Internet of Things (IoT) and sensor technology development service',
            'Quantum computing and quantum cryptography development service'
        ];

        $startDate = now()->subMonths(fake()->numberBetween(1, 6));

        $projectName = fake()->unique(true)->randomElement($projectArray);
        /* @phpstan-ignore-line */

        return [
            'project_name' => $projectName,
            'project_summary' => fake()->paragraph,
            'start_date' => $startDate->format('Y-m-d'),
            'deadline' => $startDate->addMonths(4)->format('Y-m-d'),
            'notes' => fake()->paragraph,
            'completion_percent' => fake()->numberBetween(40, 100),
            'feedback' => fake()->realText(),
            'project_short_code' => substr($this->initials($projectName), 0, 3),
            'calculate_task_progress' => 'false',
        ];
    }

    protected function initials($str): string
    {
        $str = preg_replace('/\s+/', ' ', $str);
        $ret = '';

        $array = explode(' ', $str);

        if (count($array) === 1 || count($array) === 0) {
            return $this->clean(strtoupper(substr($str, -4)));
        }

        foreach ($array as $word) {
            $ret .= strtoupper($word[0]);
        }

        return $this->clean($ret);
    }

    protected function clean($string): array|string|null
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

}
