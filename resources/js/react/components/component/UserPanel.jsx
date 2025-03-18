
import React from 'react';
import { useExam } from '../hooks/useExam';
import { Separator } from '../ui/separator';
import { Button } from '../ui/button';
import { Card } from '../ui/card';
import { LogOut } from 'lucide-react';

const UserPanel = ({ userData }) => {
  const { exam, resetExam } = useExam();

  const handleExit = () => {
    if (window.confirm('Are you sure you want to exit this exam? Your progress will be saved.')) {
      resetExam();
    }
  };

  return (
    <div className="glass-panel h-full flex flex-col">
      <div className="p-4 border-b border-border">
        <h3 className="text-sm font-semibold">Candidate Information</h3>
      </div>

      <div className="flex-1 p-4 space-y-4 overflow-y-auto">
        <div className="flex items-center space-x-3">
          {userData.profileImage ? (
            <div className="h-16 w-16 rounded-full overflow-hidden bg-secondary flex-shrink-0 border border-border">
              <img
                src={userData.profileImage}
                alt={userData.name}
                className="h-full w-full object-cover"
              />
            </div>
          ) : (
            <div className="h-16 w-16 rounded-full bg-secondary flex items-center justify-center flex-shrink-0 border border-border">
              <span className="text-xl font-semibold text-muted-foreground">
                {userData.name.charAt(0)}
              </span>
            </div>
          )}

          <div className="flex-1 min-w-0">
            <h4 className="font-medium text-foreground truncate">{userData.name}</h4>
            <p className="text-sm text-muted-foreground truncate">{userData.email}</p>
          </div>
        </div>

        <Separator />

        <div className="space-y-3">
          <div>
            <h5 className="text-xs font-medium text-muted-foreground mb-1">Exam ID</h5>
            <p className="text-sm font-mono">{userData.examId}</p>
          </div>

          <div>
            <h5 className="text-xs font-medium text-muted-foreground mb-1">Exam Title</h5>
            <p className="text-sm">{exam.title}</p>
          </div>

          <div>
            <h5 className="text-xs font-medium text-muted-foreground mb-1">Duration</h5>
            <p className="text-sm">{exam.timeLimit} minutes</p>
          </div>

          <div>
            <h5 className="text-xs font-medium text-muted-foreground mb-1">Total Questions</h5>
            <p className="text-sm">{exam.questions.length}</p>
          </div>

          <div>
            <h5 className="text-xs font-medium text-muted-foreground mb-1">Passing Score</h5>
            <p className="text-sm">{exam.passingScore}%</p>
          </div>

          <div>
            <h5 className="text-xs font-medium text-muted-foreground mb-1">Class</h5>
            <p className="text-sm">{userData.class.name}</p>
          </div>
        </div>
      </div>

      <div className="p-4 border-t border-border">
        <Button
          variant="outline"
          className="w-full text-xs"
          onClick={handleExit}
        >
          <LogOut className="h-3.5 w-3.5 mr-1.5" />
          Exit Exam
        </Button>
      </div>
    </div>
  );
};

export default UserPanel;
