-- Database tables for lesson system

CREATE TABLE IF NOT EXISTS lessons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  starter_code TEXT,
  expected_output TEXT,
  order_no INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  lesson_id INT NOT NULL,
  completed BOOLEAN DEFAULT FALSE,
  completed_at TIMESTAMP NULL,
  UNIQUE KEY unique_user_lesson (user_id, lesson_id),
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Insert sample lesson
INSERT INTO lessons (title, content, starter_code, expected_output, order_no) VALUES
('Shooting Star', 
 '<h2>Write the date</h2><p>Write your wish using HTML tags</p><p>Use &lt;h2&gt; for the date and &lt;p&gt; for your wish.</p>',
 '<!-- Write code below -->\n\n',
 '<h2>2025-01-13</h2>\n<p>I want to be a developer</p>',
 1
);

INSERT INTO lessons (title, content, starter_code, expected_output, order_no) VALUES
('Hello World', 
 '<h2>Your First HTML</h2><p>Create a simple heading that says "Hello World"</p><p>Use the &lt;h1&gt; tag.</p>',
 '<!-- Write your code here -->\n\n',
 '<h1>Hello World</h1>',
 2
);

INSERT INTO lessons (title, content, starter_code, expected_output, order_no) VALUES
('Profile Card', 
 '<h2>Build Your Profile</h2><p>Create a profile card with your name in an &lt;h3&gt; tag and a short bio in a &lt;p&gt; tag.</p>',
 '<!-- Create your profile -->\n\n',
 '<h3>John Doe</h3>\n<p>I am learning web development</p>',
 3
);
