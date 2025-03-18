
import React from 'react';
import { useExam } from '../hooks/useExam';
import { Button } from '../ui/button';
import { Card } from '../ui/card';
import { ArrowLeft, ArrowRight } from 'lucide-react';

const QuestionCard = ({
    question,
    questionNumber,
    totalQuestions
  }) => {
    const { saveAnswer, answers, goToNextQuestion, goToPreviousQuestion } = useExam();

    // Find the user's answer for this question, if any
    const userAnswer = answers.find(a => a.questionId === question.id);

    const handleOptionSelect = (optionId) => {
      saveAnswer(question.id, optionId);
    };

    return (
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <span className="inline-block px-2.5 py-1 bg-secondary rounded-md text-xs font-medium">
              Question {questionNumber} of {totalQuestions}
            </span>
          </div>
        </div>

        <Card className="p-6 shadow-sm">
          <h2 className="text-xl font-medium mb-4">{question.text}</h2>

          {/* Question Image (if available) */}
          {question.image && (
            <div className="mb-6">
              <AspectRatio ratio={16 / 9} className="bg-muted rounded-md overflow-hidden">
                <img
                  src={question.image}
                  alt="Question illustration"
                  className="w-full h-full object-contain"
                  onError={(e) => {
                    console.error("Failed to load question image");
                    e.target.style.display = 'none';
                  }}
                />
              </AspectRatio>
            </div>
          )}

          <div className="space-y-3">
            {question.options.map((option) => (
              <div
                key={option.id}
                className={`question-option ${userAnswer?.selectedOptionId === option.id ? 'question-option-selected' : ''}`}
                onClick={() => handleOptionSelect(option.id)}
              >
                <div className="mr-3 h-5 w-5 rounded-full border border-border flex items-center justify-center flex-shrink-0">
                  {userAnswer?.selectedOptionId === option.id && (
                    <div className="h-3 w-3 rounded-full bg-primary" />
                  )}
                </div>

                <div className="flex-1">
                  <span>{option.text}</span>

                  {/* Option Image (if available) */}
                  {option.image && (
                    <div className="mt-2">
                      <AspectRatio ratio={16 / 9} className="bg-muted rounded-md overflow-hidden max-w-[300px]">
                        <img
                          src={option.image}
                          alt={`Option ${option.id}`}
                          className="w-full h-full object-contain"
                          onError={(e) => {
                            console.error(`Failed to load image for option ${option.id}`);
                            e.target.style.display = 'none';
                          }}
                        />
                      </AspectRatio>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </Card>

        <div className="flex justify-between pt-2">
          <Button
            variant="outline"
            onClick={goToPreviousQuestion}
            disabled={questionNumber === 1}
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Previous
          </Button>

          <Button
            onClick={goToNextQuestion}
          >
            {questionNumber === totalQuestions ? 'Finish' : 'Next'}
            <ArrowRight className="h-4 w-4 ml-2" />
          </Button>
        </div>
      </div>
    );
  };

  export default QuestionCard;

